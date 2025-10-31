<?php

namespace Tourze\PurchaseManageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;
use Tourze\SupplierManageBundle\DataFixtures\SupplierFixtures;
use Tourze\SupplierManageBundle\Entity\Supplier;

class PurchaseOrderFixtures extends Fixture implements DependentFixtureInterface
{
    public const ORDER_REFERENCE = 'order';

    public function load(ObjectManager $manager): void
    {
        $orders = [
            ['orderNo' => 'PO202401001', 'status' => PurchaseOrderStatus::DRAFT, 'totalAmount' => 10000.00],
            ['orderNo' => 'PO202401002', 'status' => PurchaseOrderStatus::PENDING_APPROVAL, 'totalAmount' => 25000.00],
            ['orderNo' => 'PO202401003', 'status' => PurchaseOrderStatus::APPROVED, 'totalAmount' => 15000.00],
            ['orderNo' => 'PO202401004', 'status' => PurchaseOrderStatus::REJECTED, 'totalAmount' => 8000.00],
            ['orderNo' => 'PO202401005', 'status' => PurchaseOrderStatus::COMPLETED, 'totalAmount' => 30000.00],
        ];

        foreach ($orders as $index => $orderData) {
            $order = new PurchaseOrder();
            $order->setOrderNumber($orderData['orderNo']);
            $order->setStatus($orderData['status']);
            $order->setTotalAmount((string) $orderData['totalAmount']);
            $order->setTitle('采购订单 - ' . $orderData['orderNo']);
            $order->setExpectedDeliveryDate(new \DateTimeImmutable('+30 days'));

            $supplierReferences = [
                SupplierFixtures::SUPPLIER_A_REFERENCE,
                SupplierFixtures::SUPPLIER_B_REFERENCE,
                SupplierFixtures::SUPPLIER_C_REFERENCE,
            ];
            $supplierIndex = $index % count($supplierReferences);
            $supplier = $this->getReference($supplierReferences[$supplierIndex], Supplier::class);
            $order->setSupplier($supplier);

            $manager->persist($order);
            $this->addReference(self::ORDER_REFERENCE . '_' . $index, $order);
        }

        $manager->flush();
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            SupplierFixtures::class,
        ];
    }
}
