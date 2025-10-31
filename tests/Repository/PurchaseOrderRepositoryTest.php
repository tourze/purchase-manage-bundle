<?php

namespace Tourze\PurchaseManageBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;
use Tourze\PurchaseManageBundle\Repository\PurchaseOrderRepository;
use Tourze\SupplierManageBundle\Entity\Supplier;
use Tourze\SupplierManageBundle\Enum\SupplierType;

/**
 * @internal
 */
#[CoversClass(PurchaseOrderRepository::class)]
#[RunTestsInSeparateProcesses]
final class PurchaseOrderRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // AbstractRepositoryTestCase 会自动处理设置
    }

    protected function createNewEntity(): PurchaseOrder
    {
        // 创建供应商并持久化
        $supplier = new Supplier();
        $supplier->setName('Test Supplier');
        $supplier->setLegalName('Test Supplier Ltd');
        $supplier->setLegalAddress('Test Address 123');
        $supplier->setRegistrationNumber('REG' . uniqid());
        $supplier->setTaxNumber('TAX' . uniqid());
        $supplier->setSupplierType(SupplierType::SUPPLIER);

        // 持久化供应商
        self::getEntityManager()->persist($supplier);
        self::getEntityManager()->flush();

        $order = new PurchaseOrder();
        $order->setOrderNumber('PO-' . uniqid());
        $order->setTitle('Test Purchase Order');
        $order->setSupplier($supplier);
        $order->setStatus(PurchaseOrderStatus::DRAFT);
        $order->setTotalAmount('1000.00');
        $order->setCurrency('CNY');

        return $order;
    }

    /** @return PurchaseOrderRepository */
    protected function getRepository(): PurchaseOrderRepository
    {
        return self::getService(PurchaseOrderRepository::class);
    }

    public function testFindPendingApproval(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderRepository::class, $repository);
        $order = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $repository->save($order, true);

        $result = $repository->findPendingApproval();
        $this->assertIsArray($result);
    }

    public function testFindBySupplier(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderRepository::class, $repository);
        $order = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $repository->save($order, true);

        $supplierId = 1;
        $result = $repository->findBySupplier($supplierId);
        $this->assertIsArray($result);
    }

    public function testFindByStatus(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderRepository::class, $repository);
        $order = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $repository->save($order, true);

        $status = PurchaseOrderStatus::DRAFT;
        $result = $repository->findByStatus($status);
        $this->assertIsArray($result);
    }

    public function testFindByDateRange(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderRepository::class, $repository);
        $order = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $repository->save($order, true);

        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');
        $result = $repository->findByDateRange($startDate, $endDate);
        $this->assertIsArray($result);
    }

    public function testSearchOrders(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderRepository::class, $repository);
        $order = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $repository->save($order, true);

        $criteria = ['keyword' => 'Test'];
        $result = $repository->searchOrders($criteria);
        $this->assertIsArray($result);
    }

    public function testGetOrderStatistics(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderRepository::class, $repository);
        $order = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $repository->save($order, true);

        $result = $repository->getOrderStatistics();
        $this->assertIsArray($result);
    }

    public function testCountPendingOrders(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderRepository::class, $repository);
        $order = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $repository->save($order, true);

        $result = $repository->countPendingOrders();
        $this->assertIsInt($result);
    }

    public function testSave(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderRepository::class, $repository);
        $order = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $repository->save($order, true);

        $this->assertNotNull($order->getId());
    }

    public function testRemove(): void
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(PurchaseOrderRepository::class, $repository);
        $order = $this->createNewEntity();
        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $repository->save($order, true);
        $id = $order->getId();

        $repository->remove($order, true);

        $found = $repository->find($id);
        $this->assertNull($found);
    }
}
