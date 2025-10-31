<?php

namespace Tourze\PurchaseManageBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrderItem;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;
use Tourze\PurchaseManageBundle\Repository\PurchaseOrderItemRepository;
use Tourze\SupplierManageBundle\Entity\Supplier;
use Tourze\SupplierManageBundle\Enum\SupplierType;

/**
 * @internal
 */
#[CoversClass(PurchaseOrderItemRepository::class)]
#[RunTestsInSeparateProcesses]
final class PurchaseOrderItemRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // AbstractRepositoryTestCase 会自动处理设置
    }

    protected function createNewEntity(): PurchaseOrderItem
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

        // 创建采购订单（PurchaseOrderItem的必需外键）
        $order = new PurchaseOrder();
        $order->setTitle('测试采购订单 - ' . uniqid());
        $order->setSupplier($supplier);
        $order->setTotalAmount('10000.00');
        $order->setExpectedDeliveryDate(new \DateTimeImmutable('+30 days'));

        self::getEntityManager()->persist($order);

        // 创建订单项
        $item = new PurchaseOrderItem();
        $item->setPurchaseOrder($order);
        $item->setProductName('Test Product');
        $item->setProductCode('TEST-001');
        $item->setQuantity('10');
        $item->setUnitPrice('100.00');
        $item->setDeliveryStatus(DeliveryStatus::PENDING);

        return $item;
    }

    /** @return PurchaseOrderItemRepository */
    protected function getRepository(): PurchaseOrderItemRepository
    {
        return self::getService(PurchaseOrderItemRepository::class);
    }

    public function testFindPendingDelivery(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderItemRepository::class, $repository);
        $item = $this->createNewEntity();
        $repository->save($item, true);

        $result = $repository->findPendingDelivery();
        $this->assertIsArray($result);
    }

    public function testFindByProduct(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderItemRepository::class, $repository);
        $item = $this->createNewEntity();
        $repository->save($item, true);

        $spuId = 1;
        $result = $repository->findByProduct($spuId);
        $this->assertIsArray($result);
    }

    public function testGetProductStatistics(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderItemRepository::class, $repository);
        $item = $this->createNewEntity();
        $repository->save($item, true);

        $spuId = 1;
        $result = $repository->getProductStatistics($spuId);
        $this->assertIsArray($result);
    }

    public function testSave(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderItemRepository::class, $repository);
        $item = $this->createNewEntity();
        $repository->save($item, true);

        $this->assertNotNull($item->getId());
    }

    public function testRemove(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderItemRepository::class, $repository);
        $item = $this->createNewEntity();
        $repository->save($item, true);
        $id = $item->getId();

        $repository->remove($item, true);

        $found = $repository->find($id);
        $this->assertNull($found);
    }
}
