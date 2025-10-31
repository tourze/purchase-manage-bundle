<?php

namespace Tourze\PurchaseManageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;

class PurchaseDeliveryFixtures extends Fixture implements DependentFixtureInterface
{
    public const DELIVERY_REFERENCE = 'delivery';

    public function load(ObjectManager $manager): void
    {
        $deliveries = [
            [
                'orderIndex' => 2,
                'deliveryNo' => 'DLV202401001',
                'status' => DeliveryStatus::PENDING,
                'deliveryCompany' => '物流公司A',
                'trackingNo' => 'TRACK001',
            ],
            [
                'orderIndex' => 3,
                'deliveryNo' => 'DLV202401002',
                'status' => DeliveryStatus::IN_TRANSIT,
                'deliveryCompany' => '物流公司B',
                'trackingNo' => 'TRACK002',
            ],
            [
                'orderIndex' => 4,
                'deliveryNo' => 'DLV202401003',
                'status' => DeliveryStatus::RECEIVED,
                'deliveryCompany' => '物流公司C',
                'trackingNo' => 'TRACK003',
                'receivedAt' => new \DateTimeImmutable('-2 days'),
                'receivedBy' => '收货人张三',
            ],
        ];

        foreach ($deliveries as $index => $deliveryData) {
            $delivery = new PurchaseDelivery();

            $order = $this->getReference(PurchaseOrderFixtures::ORDER_REFERENCE . '_' . $deliveryData['orderIndex'], PurchaseOrder::class);
            $delivery->setPurchaseOrder($order);
            $delivery->setBatchNumber($deliveryData['deliveryNo']);
            $delivery->setStatus($deliveryData['status']);
            $delivery->setLogisticsCompany($deliveryData['deliveryCompany']);
            $delivery->setTrackingNumber($deliveryData['trackingNo']);
            $delivery->setShipTime(new \DateTimeImmutable('-5 days'));

            if (isset($deliveryData['receivedAt'])) {
                $delivery->setReceiveTime($deliveryData['receivedAt']);
            }
            if (isset($deliveryData['receivedBy'])) {
                $delivery->setReceivedBy($deliveryData['receivedBy']);
            }

            $manager->persist($delivery);
            $this->addReference(self::DELIVERY_REFERENCE . '_' . $index, $delivery);
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
