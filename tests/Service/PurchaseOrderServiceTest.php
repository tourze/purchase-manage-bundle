<?php

namespace Tourze\PurchaseManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;
use Tourze\PurchaseManageBundle\Service\PurchaseOrderService;
use Tourze\SupplierManageBundle\Entity\Supplier;
use Tourze\SupplierManageBundle\Enum\SupplierStatus;
use Tourze\SupplierManageBundle\Enum\SupplierType;

/**
 * @internal
 */
#[CoversClass(PurchaseOrderService::class)]
#[RunTestsInSeparateProcesses]
class PurchaseOrderServiceTest extends AbstractIntegrationTestCase
{
    private PurchaseOrderService $service;

    /**
     * 创建并持久化测试供应商（SupplierManageBundle）
     */
    private function createTestSupplier(string $name = '测试供应商'): Supplier
    {
        $supplier = new Supplier();
        $supplier->setName($name);
        $supplier->setLegalName($name . ' 有限公司');
        $supplier->setLegalAddress('测试地址123号');
        $supplier->setRegistrationNumber('TEST' . uniqid());
        $supplier->setTaxNumber('TAX' . uniqid());
        $supplier->setSupplierType(SupplierType::SUPPLIER);
        $supplier->setStatus(SupplierStatus::APPROVED);

        self::getEntityManager()->persist($supplier);
        self::getEntityManager()->flush();

        return $supplier;
    }

    public function testCreateOrder(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        $order = $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $this->assertEquals($supplier, $order->getSupplier());
        $this->assertEquals(PurchaseOrderStatus::DRAFT, $order->getStatus());
        $this->assertCount(1, $order->getItems());
    }

    public function testUpdateOrderStatus(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        $order = $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        $result = $this->service->updateOrderStatus($order, 'submit_for_approval');
        $this->assertTrue($result);
    }

    public function testUpdateOrderStatusCannotTransition(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        $order = $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        // 先将订单状态改为已完成
        $this->service->updateOrderStatus($order, 'submit_for_approval');
        $this->service->approveOrder($order, 1, 'Test approval');

        // 尝试无效的状态转换
        $result = $this->service->updateOrderStatus($order, 'invalid_transition');
        $this->assertFalse($result);
    }

    public function testSubmitForApproval(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        $order = $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        $result = $this->service->submitForApproval($order);
        $this->assertTrue($result);
    }

    public function testApproveOrder(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        $order = $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        $this->service->submitForApproval($order);
        $result = $this->service->approveOrder($order, 123, 'Approved');
        $this->assertTrue($result);
        $this->assertEquals(123, $order->getApprovedBy());
        $this->assertEquals('Approved', $order->getApprovalComment());
        $this->assertNotNull($order->getApproveTime());
    }

    public function testRejectOrder(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        $order = $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        $this->service->submitForApproval($order);
        $reason = 'Not enough budget';
        $result = $this->service->rejectOrder($order, $reason);
        $this->assertTrue($result);
        $this->assertEquals($reason, $order->getApprovalComment());
    }

    public function testCancelOrder(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        $order = $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        $reason = 'No longer needed';
        $result = $this->service->cancelOrder($order, $reason);
        $this->assertTrue($result);
        $this->assertEquals($reason, $order->getCancelReason());
        $this->assertNotNull($order->getCancelTime());
    }

    public function testFindPendingApprovalOrders(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        // 创建一个待审批的订单
        $order = $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);
        $this->service->submitForApproval($order);

        $result = $this->service->findPendingApprovalOrders();
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(PurchaseOrder::class, $result[0]);
    }

    public function testFindOrdersBySupplier(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        // 创建一个订单
        $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        $result = $this->service->findOrdersBySupplier($supplier);
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(PurchaseOrder::class, $result[0]);
    }

    public function testSearchOrders(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        // 创建一个订单
        $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        $criteria = ['status' => 'draft'];
        $result = $this->service->searchOrders($criteria);
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(PurchaseOrder::class, $result[0]);
    }

    public function testGetOrderStatistics(): void
    {
        $supplier = $this->createTestSupplier('Test Supplier');

        // 创建一些订单用于统计
        $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product 1',
                'quantity' => '10',
                'unitPrice' => '100',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        $this->service->createOrder($supplier, [
            [
                'productName' => 'Test Product 2',
                'quantity' => '5',
                'unitPrice' => '200',
                'unit' => 'piece',
                'taxRate' => '0.13',
            ],
        ]);

        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');
        $result = $this->service->getOrderStatistics($startDate, $endDate);
        $this->assertIsArray($result);
    }

    protected function onSetUp(): void
    {
        $this->service = self::getService(PurchaseOrderService::class);
    }
}
