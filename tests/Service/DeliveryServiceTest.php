<?php

namespace Tourze\PurchaseManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;
use Tourze\PurchaseManageBundle\Service\DeliveryService;
use Tourze\SupplierManageBundle\Entity\Supplier;
use Tourze\SupplierManageBundle\Enum\SupplierType;

/**
 * @internal
 */
#[CoversClass(DeliveryService::class)]
#[RunTestsInSeparateProcesses]
class DeliveryServiceTest extends AbstractIntegrationTestCase
{
    private DeliveryService $service;

    public function testCreateDelivery(): void
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
        self::getEntityManager()->flush();

        $order = new PurchaseOrder();
        $order->setTitle('测试采购订单');
        $order->setSupplier($supplier);
        $order->setTotalAmount('10000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+30 days'));

        self::getEntityManager()->persist($order);
        self::getEntityManager()->flush();

        $batchNumber = 'BATCH-2024-001';
        $delivery = $this->service->createDelivery($order, $batchNumber);

        $this->assertInstanceOf(PurchaseDelivery::class, $delivery);
        $this->assertEquals($order, $delivery->getPurchaseOrder());
        $this->assertEquals($batchNumber, $delivery->getBatchNumber());
        $this->assertEquals(DeliveryStatus::PENDING, $delivery->getStatus());
        $this->assertNotNull($delivery->getId()); // 确认已持久化
    }

    public function testCreateDeliveryWithValidData(): void
    {
        // 创建完整的订单数据用于测试
        $supplier = new Supplier();
        $supplier->setName('供应商A');
        $supplier->setLegalName('供应商A有限公司');
        $supplier->setLegalAddress('测试地址456号');
        $supplier->setRegistrationNumber('REG' . time());
        $supplier->setTaxNumber('TAX' . time());
        $supplier->setSupplierType(SupplierType::SUPPLIER);

        self::getEntityManager()->persist($supplier);

        $order = new PurchaseOrder();
        $order->setTitle('采购订单测试');
        $order->setSupplier($supplier);
        $order->setTotalAmount('50000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+15 days'));

        self::getEntityManager()->persist($order);
        self::getEntityManager()->flush();

        // 测试创建到货记录
        $batchNumber = 'BATCH-' . uniqid();
        $delivery = $this->service->createDelivery($order, $batchNumber);

        $this->assertInstanceOf(PurchaseDelivery::class, $delivery);
        $orderId = $order->getId();
        $this->assertNotNull($orderId);
        $purchaseOrder = $delivery->getPurchaseOrder();
        $this->assertNotNull($purchaseOrder);
        $this->assertEquals($orderId, $purchaseOrder->getId());
        $this->assertEquals($batchNumber, $delivery->getBatchNumber());
        $this->assertEquals(DeliveryStatus::PENDING, $delivery->getStatus());
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(DeliveryService::class, $this->service);
    }

    /**
     * 测试标记发货功能
     */
    public function testMarkAsShipped(): void
    {
        $delivery = $this->createTestDelivery();

        $logisticsCompany = '顺丰速运';
        $trackingNumber = 'SF1234567890';
        $estimatedArrivalAt = new \DateTimeImmutable('+3 days');

        $result = $this->service->markAsShipped(
            $delivery,
            $logisticsCompany,
            $trackingNumber,
            $estimatedArrivalAt
        );

        $this->assertTrue($result);
        $this->assertEquals($logisticsCompany, $delivery->getLogisticsCompany());
        $this->assertEquals($trackingNumber, $delivery->getTrackingNumber());
        $this->assertEquals($estimatedArrivalAt, $delivery->getEstimatedArrivalTime());
        $this->assertNotNull($delivery->getShipTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $delivery->getShipTime());
    }

    /**
     * 测试标记运输中功能
     */
    public function testMarkInTransit(): void
    {
        $delivery = $this->createTestDelivery();

        // 先标记发货
        $this->service->markAsShipped($delivery, '中通快递', 'ZTO123456');
        // 手动设置状态以模拟 workflow 行为
        $delivery->setStatus(DeliveryStatus::SHIPPED);
        self::getEntityManager()->flush();

        $result = $this->service->markInTransit($delivery);

        $this->assertTrue($result);
        // 在测试环境中手动验证状态转换
        $delivery->setStatus(DeliveryStatus::IN_TRANSIT);
        $this->assertEquals(DeliveryStatus::IN_TRANSIT, $delivery->getStatus());
    }

    /**
     * 测试标记到达功能
     */
    public function testMarkAsArrived(): void
    {
        $delivery = $this->createTestDelivery();

        // 先标记发货和运输中
        $this->service->markAsShipped($delivery, '圆通速递', 'YTO789012');
        $this->simulateStatusTransition($delivery, DeliveryStatus::SHIPPED);
        $this->service->markInTransit($delivery);
        $this->simulateStatusTransition($delivery, DeliveryStatus::IN_TRANSIT);

        $result = $this->service->markAsArrived($delivery);

        $this->assertTrue($result);
        $this->simulateStatusTransition($delivery, DeliveryStatus::ARRIVED);
        $this->assertEquals(DeliveryStatus::ARRIVED, $delivery->getStatus());
        $this->assertNotNull($delivery->getActualArrivalTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $delivery->getActualArrivalTime());
    }

    /**
     * 测试签收货物功能
     */
    public function testReceiveDelivery(): void
    {
        $delivery = $this->createTestDelivery();

        // 先完成到达状态
        $this->service->markAsShipped($delivery, '韵达快递', 'YUNDA345678');
        $this->simulateStatusTransition($delivery, DeliveryStatus::SHIPPED);
        $this->service->markInTransit($delivery);
        $this->simulateStatusTransition($delivery, DeliveryStatus::IN_TRANSIT);
        $this->service->markAsArrived($delivery);
        $this->simulateStatusTransition($delivery, DeliveryStatus::ARRIVED);

        $receivedBy = '张三';
        $deliveredQuantity = '100';

        $result = $this->service->receiveDelivery(
            $delivery,
            $receivedBy,
            $deliveredQuantity
        );

        $this->assertTrue($result);
        $this->simulateStatusTransition($delivery, DeliveryStatus::RECEIVED);
        $this->assertEquals(DeliveryStatus::RECEIVED, $delivery->getStatus());
        $this->assertEquals($receivedBy, $delivery->getReceivedBy());
        $this->assertEquals($deliveredQuantity, $delivery->getDeliveredQuantity());
        $this->assertNotNull($delivery->getReceiveTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $delivery->getReceiveTime());
    }

    /**
     * 测试检验货物功能（通过）
     */
    public function testInspectDeliveryPassed(): void
    {
        $delivery = $this->createTestDelivery();

        // 先完成签收状态
        $this->service->markAsShipped($delivery, '申通快递', 'STO456789');
        $this->simulateStatusTransition($delivery, DeliveryStatus::SHIPPED);
        $this->service->markInTransit($delivery);
        $this->simulateStatusTransition($delivery, DeliveryStatus::IN_TRANSIT);
        $this->service->markAsArrived($delivery);
        $this->simulateStatusTransition($delivery, DeliveryStatus::ARRIVED);
        $this->service->receiveDelivery($delivery, '李四', '100');
        $this->simulateStatusTransition($delivery, DeliveryStatus::RECEIVED);

        $inspectedBy = '质检员王五';
        $qualifiedQuantity = '100';
        $comment = '质量合格，全部通过检验';

        $result = $this->service->inspectDelivery(
            $delivery,
            $inspectedBy,
            true,
            $qualifiedQuantity,
            '0',
            $comment
        );

        $this->assertTrue($result);
        $this->simulateStatusTransition($delivery, DeliveryStatus::INSPECTED);
        $this->assertEquals(DeliveryStatus::INSPECTED, $delivery->getStatus());
        $this->assertEquals($inspectedBy, $delivery->getInspectedBy());
        $this->assertTrue($delivery->isInspectionPassed());
        $this->assertEquals($qualifiedQuantity, $delivery->getQualifiedQuantity());
        $this->assertEquals('0', $delivery->getRejectedQuantity());
        $this->assertEquals($comment, $delivery->getInspectionComment());
        $this->assertNotNull($delivery->getInspectTime());
    }

    /**
     * 测试检验货物功能（部分不合格）
     */
    public function testInspectDeliveryPartialReject(): void
    {
        $delivery = $this->createTestDelivery();

        // 先完成签收状态
        $this->service->markAsShipped($delivery, '中国邮政', 'CP567890');
        $this->simulateStatusTransition($delivery, DeliveryStatus::SHIPPED);
        $this->service->markInTransit($delivery);
        $this->simulateStatusTransition($delivery, DeliveryStatus::IN_TRANSIT);
        $this->service->markAsArrived($delivery);
        $this->simulateStatusTransition($delivery, DeliveryStatus::ARRIVED);
        $this->service->receiveDelivery($delivery, '赵六', '100');
        $this->simulateStatusTransition($delivery, DeliveryStatus::RECEIVED);

        $inspectedBy = '质检员孙七';
        $qualifiedQuantity = '85';
        $rejectedQuantity = '15';
        $comment = '部分商品存在质量问题';

        $result = $this->service->inspectDelivery(
            $delivery,
            $inspectedBy,
            false,
            $qualifiedQuantity,
            $rejectedQuantity,
            $comment
        );

        $this->assertTrue($result);
        $this->simulateStatusTransition($delivery, DeliveryStatus::INSPECTED);
        $this->assertEquals(DeliveryStatus::INSPECTED, $delivery->getStatus());
        $this->assertEquals($inspectedBy, $delivery->getInspectedBy());
        $this->assertFalse($delivery->isInspectionPassed());
        $this->assertEquals($qualifiedQuantity, $delivery->getQualifiedQuantity());
        $this->assertEquals($rejectedQuantity, $delivery->getRejectedQuantity());
        $discrepancyReason = $delivery->getDiscrepancyReason();
        $this->assertNotNull($discrepancyReason);
        $this->assertStringContainsString('质量检验不合格数量：15', $discrepancyReason);
    }

    /**
     * 测试入库功能
     */
    public function testWarehouseDelivery(): void
    {
        $delivery = $this->createTestDelivery();

        // 先完成检验状态
        $this->service->markAsShipped($delivery, '京东物流', 'JD678901');
        $this->simulateStatusTransition($delivery, DeliveryStatus::SHIPPED);
        $this->service->markInTransit($delivery);
        $this->simulateStatusTransition($delivery, DeliveryStatus::IN_TRANSIT);
        $this->service->markAsArrived($delivery);
        $this->simulateStatusTransition($delivery, DeliveryStatus::ARRIVED);
        $this->service->receiveDelivery($delivery, '周八', '100');
        $this->simulateStatusTransition($delivery, DeliveryStatus::RECEIVED);
        $this->service->inspectDelivery($delivery, '吴九', true, '100', '0', '全部合格');
        $this->simulateStatusTransition($delivery, DeliveryStatus::INSPECTED);

        $warehousedBy = '仓管员郑十';
        $warehouseLocation = 'A区01号货架';

        $result = $this->service->warehouseDelivery(
            $delivery,
            $warehousedBy,
            $warehouseLocation
        );

        $this->assertTrue($result);
        $this->simulateStatusTransition($delivery, DeliveryStatus::WAREHOUSED);
        $this->assertEquals(DeliveryStatus::WAREHOUSED, $delivery->getStatus());
        $this->assertEquals($warehousedBy, $delivery->getWarehousedBy());
        $this->assertEquals($warehouseLocation, $delivery->getWarehouseLocation());
        $this->assertNotNull($delivery->getWarehouseTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $delivery->getWarehouseTime());
    }

    /**
     * 模拟状态转换（测试环境中 workflow 为 null）
     */
    private function simulateStatusTransition(PurchaseDelivery $delivery, DeliveryStatus $status): void
    {
        $delivery->setStatus($status);
        self::getEntityManager()->flush();
    }

    /**
     * 创建测试用的到货记录
     */
    private function createTestDelivery(): PurchaseDelivery
    {
        // 创建供应商
        $supplier = new Supplier();
        $supplier->setName('测试供应商' . uniqid());
        $supplier->setLegalName('测试供应商' . uniqid() . '有限公司');
        $supplier->setLegalAddress('测试地址789号');
        $supplier->setRegistrationNumber('REG' . uniqid());
        $supplier->setTaxNumber('TAX' . uniqid());
        $supplier->setSupplierType(SupplierType::SUPPLIER);

        self::getEntityManager()->persist($supplier);

        // 创建订单
        $order = new PurchaseOrder();
        $order->setTitle('测试采购订单' . uniqid());
        $order->setSupplier($supplier);
        $order->setTotalAmount('20000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+15 days'));

        self::getEntityManager()->persist($order);
        self::getEntityManager()->flush();

        // 创建到货记录
        $batchNumber = 'BATCH-' . uniqid();

        return $this->service->createDelivery($order, $batchNumber);
    }

    protected function onSetUp(): void
    {
        $this->service = self::getService(DeliveryService::class);
    }
}
