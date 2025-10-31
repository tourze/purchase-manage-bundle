<?php

namespace Tourze\PurchaseManageBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;
use Tourze\PurchaseManageBundle\Repository\PurchaseDeliveryRepository;
use Tourze\SupplierManageBundle\Entity\Supplier;
use Tourze\SupplierManageBundle\Enum\SupplierType;

/**
 * @internal
 */
#[CoversClass(PurchaseDeliveryRepository::class)]
#[RunTestsInSeparateProcesses]
final class PurchaseDeliveryRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // AbstractRepositoryTestCase 会自动处理设置
    }

    protected function createNewEntity(): PurchaseDelivery
    {
        // 创建供应商（必需的依赖）
        $supplier = new Supplier();
        $supplier->setName('测试供应商');
        $supplier->setLegalName('测试供应商有限公司');
        $supplier->setLegalAddress('测试地址123号');
        $supplier->setRegistrationNumber('REG' . uniqid());
        $supplier->setTaxNumber('TAX' . uniqid());
        $supplier->setSupplierType(SupplierType::SUPPLIER);

        self::getEntityManager()->persist($supplier);

        // 创建采购订单（PurchaseDelivery的必需外键）
        $order = new PurchaseOrder();
        $order->setTitle('测试采购订单 - ' . uniqid());
        $order->setSupplier($supplier);
        $order->setTotalAmount('10000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+30 days'));

        self::getEntityManager()->persist($order);

        // 创建到货记录
        $delivery = new PurchaseDelivery();
        $delivery->setPurchaseOrder($order);
        $delivery->setBatchNumber('TEST-' . uniqid());
        $delivery->setStatus(DeliveryStatus::PENDING);
        $delivery->setDeliveredQuantity('10');

        return $delivery;
    }

    /** @return PurchaseDeliveryRepository */
    protected function getRepository(): PurchaseDeliveryRepository
    {
        return self::getService(PurchaseDeliveryRepository::class);
    }

    public function testFindInTransit(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseDeliveryRepository::class, $repository);
        $delivery = $this->createNewEntity();
        $repository->save($delivery, true);

        $result = $repository->findInTransit();
        $this->assertIsArray($result);
    }

    public function testFindPendingInspection(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseDeliveryRepository::class, $repository);
        $delivery = $this->createNewEntity();
        $repository->save($delivery, true);

        $result = $repository->findPendingInspection();
        $this->assertIsArray($result);
    }

    public function testFindPendingWarehousing(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseDeliveryRepository::class, $repository);
        $delivery = $this->createNewEntity();
        $repository->save($delivery, true);

        $result = $repository->findPendingWarehousing();
        $this->assertIsArray($result);
    }

    public function testFindByOrder(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseDeliveryRepository::class, $repository);
        $delivery = $this->createNewEntity();
        $repository->save($delivery, true);

        $orderId = 1;
        $result = $repository->findByOrder($orderId);
        $this->assertIsArray($result);
    }

    public function testGetDeliveryStatistics(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseDeliveryRepository::class, $repository);
        $delivery = $this->createNewEntity();
        $repository->save($delivery, true);

        $result = $repository->getDeliveryStatistics();
        $this->assertIsArray($result);
    }

    public function testSave(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseDeliveryRepository::class, $repository);
        $delivery = $this->createNewEntity();
        $repository->save($delivery, true);

        $this->assertNotNull($delivery->getId());
    }

    public function testRemove(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseDeliveryRepository::class, $repository);
        $delivery = $this->createNewEntity();
        $repository->save($delivery, true);
        $id = $delivery->getId();

        $repository->remove($delivery, true);

        $found = $repository->find($id);
        $this->assertNull($found);
    }
}
