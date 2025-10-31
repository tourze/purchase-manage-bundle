<?php

namespace Tourze\PurchaseManageBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseApproval;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\ApprovalStatus;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;
use Tourze\PurchaseManageBundle\Repository\PurchaseApprovalRepository;
use Tourze\SupplierManageBundle\Entity\Supplier;
use Tourze\SupplierManageBundle\Enum\SupplierType;

/**
 * @internal
 */
#[CoversClass(PurchaseApprovalRepository::class)]
#[RunTestsInSeparateProcesses]
final class PurchaseApprovalRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // AbstractRepositoryTestCase 会自动处理设置

        // 手动创建fixture数据以满足基类测试要求
        $this->createFixtureData();
    }

    private function createFixtureData(): void
    {
        $entityManager = self::getEntityManager();

        // 检查是否已经有数据，避免重复创建
        $repository = self::getService(PurchaseApprovalRepository::class);
        $existingCount = $repository->count([]);
        if ($existingCount > 0) {
            return; // 数据已存在，跳过创建
        }

        // 使用时间戳确保唯一性
        $timestamp = time();

        // 创建供应商数据
        $suppliers = [
            ['name' => '供应商A', 'code' => 'SUPPLIER001_' . $timestamp, 'contact' => '张三', 'phone' => '13800138001', 'email' => 'supplier1@tourze.com'],
            ['name' => '供应商B', 'code' => 'SUPPLIER002_' . $timestamp, 'contact' => '李四', 'phone' => '13800138002', 'email' => 'supplier2@tourze.com'],
            ['name' => '供应商C', 'code' => 'SUPPLIER003_' . $timestamp, 'contact' => '王五', 'phone' => '13800138003', 'email' => 'supplier3@tourze.com'],
        ];

        $supplierEntities = [];
        foreach ($suppliers as $supplierData) {
            $supplier = new Supplier();
            $supplier->setName($supplierData['name']);
            $supplier->setLegalName($supplierData['name'] . '有限公司');
            $supplier->setLegalAddress('测试地址' . $supplierData['code']);
            $supplier->setRegistrationNumber('REG-' . $supplierData['code']);
            $supplier->setTaxNumber('TAX-' . $supplierData['code']);
            $supplier->setSupplierType(SupplierType::SUPPLIER);

            $entityManager->persist($supplier);
            $supplierEntities[] = $supplier;
        }

        // 创建采购订单数据
        $orders = [
            ['orderNo' => 'PO202401001_' . $timestamp, 'status' => PurchaseOrderStatus::DRAFT, 'totalAmount' => 10000.00],
            ['orderNo' => 'PO202401002_' . $timestamp, 'status' => PurchaseOrderStatus::PENDING_APPROVAL, 'totalAmount' => 25000.00],
            ['orderNo' => 'PO202401003_' . $timestamp, 'status' => PurchaseOrderStatus::APPROVED, 'totalAmount' => 15000.00],
            ['orderNo' => 'PO202401004_' . $timestamp, 'status' => PurchaseOrderStatus::REJECTED, 'totalAmount' => 8000.00],
            ['orderNo' => 'PO202401005_' . $timestamp, 'status' => PurchaseOrderStatus::COMPLETED, 'totalAmount' => 30000.00],
        ];

        $orderEntities = [];
        foreach ($orders as $index => $orderData) {
            $order = new PurchaseOrder();
            $order->setOrderNumber($orderData['orderNo']);
            $order->setStatus($orderData['status']);
            $order->setTotalAmount((string) $orderData['totalAmount']);
            $order->setTitle('采购订单 - ' . $orderData['orderNo']);
            $order->setExpectedDeliveryDate(new \DateTimeImmutable('+30 days'));

            $supplierIndex = $index % 3;
            $order->setSupplier($supplierEntities[$supplierIndex]);

            $entityManager->persist($order);
            $orderEntities[] = $order;
        }

        // 创建采购审批数据
        $approvals = [
            [
                'orderIndex' => 0,
                'status' => ApprovalStatus::APPROVED,
                'comment' => '符合采购标准，批准采购',
                'approverName' => '审批人A',
            ],
            [
                'orderIndex' => 1,
                'status' => ApprovalStatus::PENDING,
                'comment' => '待审批',
                'approverName' => '审批人B',
            ],
            [
                'orderIndex' => 2,
                'status' => ApprovalStatus::REJECTED,
                'comment' => '预算超支，暂不批准',
                'approverName' => '审批人C',
            ],
        ];

        foreach ($approvals as $index => $approvalData) {
            $approval = new PurchaseApproval();

            $approval->setPurchaseOrder($orderEntities[$approvalData['orderIndex']]);
            $approval->setLevel((string) ($index + 1));
            $approval->setSequence($index + 1);
            $approval->setStatus($approvalData['status']);
            $approval->setComment($approvalData['comment']);
            $approval->setApproverName($approvalData['approverName']);
            $approval->setApproverRole('ROLE_ADMIN');
            $approval->setApproverId($index + 1);
            $approval->setAmountLimit('10000.00');
            $approval->setApproveTime(new \DateTimeImmutable());

            $entityManager->persist($approval);
        }

        $entityManager->flush();
    }

    protected function createNewEntity(): PurchaseApproval
    {
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
        $order->setTotalAmount('10000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+30 days'));

        self::getEntityManager()->persist($order);

        $approval = new PurchaseApproval();
        $approval->setPurchaseOrder($order);
        $approval->setLevel('1');
        $approval->setSequence(1);
        $approval->setAmountLimit('10000.00');
        $approval->setApproverRole('ROLE_ADMIN');
        $approval->setApproverId(1);

        return $approval;
    }

    /** @return PurchaseApprovalRepository */
    protected function getRepository(): PurchaseApprovalRepository
    {
        return self::getService(PurchaseApprovalRepository::class);
    }

    public function testFindPendingApprovals(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseApprovalRepository::class, $repository);
        $approval = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseApproval::class, $approval);
        $repository->save($approval, true);

        $result = $repository->findPendingApprovals();
        $this->assertIsArray($result);
    }

    public function testFindByOrder(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseApprovalRepository::class, $repository);
        $approval = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseApproval::class, $approval);
        $repository->save($approval, true);

        $orderId = 1;
        $result = $repository->findByOrder($orderId);
        $this->assertIsArray($result);
    }

    public function testGetApproverStatistics(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseApprovalRepository::class, $repository);
        $approval = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseApproval::class, $approval);
        $repository->save($approval, true);

        $approverId = 1;
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');

        $result = $repository->getApproverStatistics($approverId, $startDate, $endDate);
        $this->assertIsArray($result);
    }

    public function testSave(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseApprovalRepository::class, $repository);
        $approval = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseApproval::class, $approval);
        $repository->save($approval, true);

        $this->assertNotNull($approval->getId());
    }

    public function testRemove(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseApprovalRepository::class, $repository);
        $approval = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseApproval::class, $approval);
        $repository->save($approval, true);
        $id = $approval->getId();

        $repository->remove($approval, true);

        $found = $repository->find($id);
        $this->assertNull($found);
    }
}
