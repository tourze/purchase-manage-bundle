<?php

namespace Tourze\PurchaseManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseApproval;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\ApprovalStatus;
use Tourze\PurchaseManageBundle\Service\ApprovalService;
use Tourze\SupplierManageBundle\Entity\Supplier;
use Tourze\SupplierManageBundle\Enum\SupplierType;

/**
 * @internal
 */
#[CoversClass(ApprovalService::class)]
#[RunTestsInSeparateProcesses]
class ApprovalServiceTest extends AbstractIntegrationTestCase
{
    private ApprovalService $service;

    public function testCreateApprovalFlow(): void
    {
        // 创建必需的依赖实体
        $supplier = new Supplier();
        $supplier->setName('测试供应商');
        $supplier->setLegalName('测试供应商有限公司');
        $supplier->setLegalAddress('测试地址123号');
        $supplier->setRegistrationNumber('REG' . uniqid());
        $supplier->setTaxNumber('TAX' . uniqid());
        $supplier->setSupplierType(SupplierType::SUPPLIER);

        self::getEntityManager()->persist($supplier);

        $order = new PurchaseOrder();
        $order->setTitle('测试采购订单');
        $order->setSupplier($supplier);
        $order->setTotalAmount('25000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+30 days'));

        self::getEntityManager()->persist($order);
        self::getEntityManager()->flush();

        $levels = [
            ['level' => '部门经理审批', 'role' => 'ROLE_MANAGER', 'amountLimit' => '10000'],
            ['level' => '财务审批', 'role' => 'ROLE_FINANCE', 'amountLimit' => '50000'],
        ];

        $approvals = $this->service->createApprovalFlow($order, $levels);

        $this->assertCount(2, $approvals);
        $this->assertEquals('部门经理审批', $approvals[0]->getLevel());
        $this->assertEquals('财务审批', $approvals[1]->getLevel());
        $this->assertEquals(1, $approvals[0]->getSequence());
        $this->assertEquals(2, $approvals[1]->getSequence());

        // 验证关联设置正确
        $orderId = $order->getId();
        $this->assertNotNull($orderId);
        foreach ($approvals as $approval) {
            $purchaseOrder = $approval->getPurchaseOrder();
            $this->assertNotNull($purchaseOrder);
            $this->assertEquals($orderId, $purchaseOrder->getId());
            $this->assertNotNull($approval->getId()); // 确认已持久化
        }
    }

    public function testGetApprovalLevelsByAmountUnder10000(): void
    {
        $levels = $this->service->getApprovalLevelsByAmount('5000');

        $this->assertCount(1, $levels);
        $this->assertEquals('部门经理审批', $levels[0]['level']);
        $this->assertEquals('ROLE_MANAGER', $levels[0]['role']);
        $this->assertEquals('10000', $levels[0]['amountLimit']);
    }

    public function testGetApprovalLevelsByAmountUnder50000(): void
    {
        $levels = $this->service->getApprovalLevelsByAmount('30000');

        $this->assertCount(2, $levels);
        $this->assertEquals('部门经理审批', $levels[0]['level']);
        $this->assertEquals('财务审批', $levels[1]['level']);
    }

    public function testGetApprovalLevelsByAmountOver50000(): void
    {
        $levels = $this->service->getApprovalLevelsByAmount('100000');

        $this->assertCount(3, $levels);
        $this->assertEquals('部门经理审批', $levels[0]['level']);
        $this->assertEquals('财务审批', $levels[1]['level']);
        $this->assertEquals('总经理审批', $levels[2]['level']);
        $this->assertNull($levels[2]['amountLimit']);
    }

    public function testGetApprovalHistory(): void
    {
        // 创建测试数据
        $supplier = new Supplier();
        $supplier->setName('供应商B');
        $supplier->setLegalName('供应商B有限公司');
        $supplier->setLegalAddress('测试地址456号');
        $supplier->setRegistrationNumber('REG' . time());
        $supplier->setTaxNumber('TAX' . time());
        $supplier->setSupplierType(SupplierType::SUPPLIER);

        self::getEntityManager()->persist($supplier);

        $order = new PurchaseOrder();
        $order->setTitle('审批历史测试订单');
        $order->setSupplier($supplier);
        $order->setTotalAmount('15000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+20 days'));

        self::getEntityManager()->persist($order);
        self::getEntityManager()->flush();

        // 创建审批流程
        $levels = [
            ['level' => '部门经理审批', 'role' => 'ROLE_MANAGER', 'amountLimit' => '10000'],
        ];

        $approvals = $this->service->createApprovalFlow($order, $levels);

        // 获取审批历史
        $history = $this->service->getApprovalHistory($order);

        $this->assertNotEmpty($history);
        $this->assertContainsOnlyInstancesOf(PurchaseApproval::class, $history);
        $this->assertEquals($approvals[0]->getId(), $history[0]->getId());
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(ApprovalService::class, $this->service);
    }

    /**
     * 测试审批处理功能
     */
    public function testProcessApproval(): void
    {
        // 创建必需的依赖实体
        $supplier = new Supplier();
        $supplier->setName('审批测试供应商');
        $supplier->setLegalName('审批测试供应商有限公司');
        $supplier->setLegalAddress('测试地址789号');
        $supplier->setRegistrationNumber('REG' . uniqid());
        $supplier->setTaxNumber('TAX' . uniqid());
        $supplier->setSupplierType(SupplierType::SUPPLIER);

        self::getEntityManager()->persist($supplier);

        $order = new PurchaseOrder();
        $order->setTitle('审批流程测试订单');
        $order->setSupplier($supplier);
        $order->setTotalAmount('35000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+25 days'));

        self::getEntityManager()->persist($order);
        self::getEntityManager()->flush();

        // 创建审批流程
        $levels = [
            ['level' => '部门经理审批', 'role' => 'ROLE_MANAGER', 'amountLimit' => '50000'],
        ];

        $approvals = $this->service->createApprovalFlow($order, $levels);
        $approval = $approvals[0];

        // 测试审批通过
        $approverId = 1001;
        $comment = '审批通过，金额合理';
        $result = $this->service->processApproval($approval, $approverId, true, $comment);

        $this->assertTrue($result);
        $this->assertEquals($approverId, $approval->getApproverId());
        $this->assertEquals($comment, $approval->getComment());
        $this->assertNotNull($approval->getApproveTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $approval->getApproveTime());
    }

    /**
     * 测试审批拒绝功能
     */
    public function testProcessApprovalReject(): void
    {
        // 创建测试数据
        $supplier = new Supplier();
        $supplier->setName('拒绝测试供应商');
        $supplier->setLegalName('拒绝测试供应商有限公司');
        $supplier->setLegalAddress('测试地址101号');
        $supplier->setRegistrationNumber('REG' . uniqid());
        $supplier->setTaxNumber('TAX' . uniqid());
        $supplier->setSupplierType(SupplierType::SUPPLIER);

        self::getEntityManager()->persist($supplier);

        $order = new PurchaseOrder();
        $order->setTitle('审批拒绝测试订单');
        $order->setSupplier($supplier);
        $order->setTotalAmount('80000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+20 days'));

        self::getEntityManager()->persist($order);
        self::getEntityManager()->flush();

        // 创建审批流程
        $levels = [
            ['level' => '财务审批', 'role' => 'ROLE_FINANCE', 'amountLimit' => '100000'],
        ];

        $approvals = $this->service->createApprovalFlow($order, $levels);
        $approval = $approvals[0];

        // 测试审批拒绝
        $approverId = 1002;
        $comment = '预算不足，拒绝此采购申请';
        $result = $this->service->processApproval($approval, $approverId, false, $comment);

        $this->assertTrue($result);
        $this->assertEquals($approverId, $approval->getApproverId());
        $this->assertEquals($comment, $approval->getComment());
        $this->assertNotNull($approval->getApproveTime());
    }

    /**
     * 测试重复处理审批（应该失败）
     */
    public function testProcessApprovalAlreadyProcessed(): void
    {
        // 创建测试数据
        $supplier = new Supplier();
        $supplier->setName('重复处理供应商');
        $supplier->setLegalName('重复处理供应商有限公司');
        $supplier->setLegalAddress('测试地址202号');
        $supplier->setRegistrationNumber('REG' . uniqid());
        $supplier->setTaxNumber('TAX' . uniqid());
        $supplier->setSupplierType(SupplierType::SUPPLIER);

        self::getEntityManager()->persist($supplier);

        $order = new PurchaseOrder();
        $order->setTitle('重复处理测试订单');
        $order->setSupplier($supplier);
        $order->setTotalAmount('15000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+30 days'));

        self::getEntityManager()->persist($order);
        self::getEntityManager()->flush();

        // 创建审批流程
        $levels = [
            ['level' => '部门经理审批', 'role' => 'ROLE_MANAGER', 'amountLimit' => '20000'],
        ];

        $approvals = $this->service->createApprovalFlow($order, $levels);
        $approval = $approvals[0];

        // 第一次处理审批
        $firstResult = $this->service->processApproval($approval, 1003, true, '第一次审批');
        $this->assertTrue($firstResult);

        // 在测试环境中，workflow 为 null，所以需要手动设置状态以模拟真实情况
        $approval->setStatus(ApprovalStatus::APPROVED);
        self::getEntityManager()->flush();

        // 尝试第二次处理同一审批（应该失败）
        $secondResult = $this->service->processApproval($approval, 1004, false, '第二次审批');
        $this->assertFalse($secondResult);

        // 确保审批信息没有被第二次操作覆盖
        $this->assertEquals(1003, $approval->getApproverId());
        $this->assertEquals('第一次审批', $approval->getComment());
    }

    protected function onSetUp(): void
    {
        $this->service = self::getService(ApprovalService::class);
    }
}
