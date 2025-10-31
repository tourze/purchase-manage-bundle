<?php

namespace Tourze\PurchaseManageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrderItem;

class PurchaseOrderItemFixtures extends Fixture implements DependentFixtureInterface
{
    public const ORDER_ITEM_REFERENCE = 'order-item';

    public function load(ObjectManager $manager): void
    {
        $items = [
            ['name' => '商品A', 'specification' => '规格1', 'unit' => '个', 'quantity' => 100, 'unitPrice' => 50.00],
            ['name' => '商品B', 'specification' => '规格2', 'unit' => '箱', 'quantity' => 20, 'unitPrice' => 200.00],
            ['name' => '商品C', 'specification' => '规格3', 'unit' => '件', 'quantity' => 50, 'unitPrice' => 150.00],
            ['name' => '商品D', 'specification' => '规格4', 'unit' => '套', 'quantity' => 10, 'unitPrice' => 500.00],
            ['name' => '商品E', 'specification' => '规格5', 'unit' => '包', 'quantity' => 200, 'unitPrice' => 25.00],
        ];

        $orderCount = 5;
        for ($orderIndex = 0; $orderIndex < $orderCount; ++$orderIndex) {
            $order = $this->getReference(PurchaseOrderFixtures::ORDER_REFERENCE . '_' . $orderIndex, PurchaseOrder::class);

            $itemsPerOrder = rand(1, 3);
            for ($i = 0; $i < $itemsPerOrder; ++$i) {
                $itemData = $items[array_rand($items)];

                $item = new PurchaseOrderItem();
                $item->setPurchaseOrder($order);
                $item->setProductName($itemData['name']);
                $item->setSpecification($itemData['specification']);
                $item->setUnit($itemData['unit']);
                $item->setQuantity((string) $itemData['quantity']);
                $item->setUnitPrice((string) $itemData['unitPrice']);
                // 自动计算 subtotal，不用手动设置

                $manager->persist($item);
                $this->addReference(self::ORDER_ITEM_REFERENCE . '_' . $orderIndex . '_' . $i, $item);
            }
        }

        $manager->flush();
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            PurchaseOrderFixtures::class,
        ];
    }
}
