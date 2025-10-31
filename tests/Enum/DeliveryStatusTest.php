<?php

namespace Tourze\PurchaseManageBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;

/**
 * @internal
 */
#[CoversClass(DeliveryStatus::class)]
class DeliveryStatusTest extends AbstractEnumTestCase
{
    public function testEnumValues(): void
    {
        $this->assertEquals('pending', DeliveryStatus::PENDING->value);
        $this->assertEquals('shipped', DeliveryStatus::SHIPPED->value);
        $this->assertEquals('in_transit', DeliveryStatus::IN_TRANSIT->value);
        $this->assertEquals('arrived', DeliveryStatus::ARRIVED->value);
        $this->assertEquals('received', DeliveryStatus::RECEIVED->value);
        $this->assertEquals('inspected', DeliveryStatus::INSPECTED->value);
        $this->assertEquals('warehoused', DeliveryStatus::WAREHOUSED->value);
    }

    public function testLabels(): void
    {
        $this->assertEquals('待发货', DeliveryStatus::PENDING->getLabel());
        $this->assertEquals('已发货', DeliveryStatus::SHIPPED->getLabel());
        $this->assertEquals('运输中', DeliveryStatus::IN_TRANSIT->getLabel());
        $this->assertEquals('已到达', DeliveryStatus::ARRIVED->getLabel());
        $this->assertEquals('已收货', DeliveryStatus::RECEIVED->getLabel());
        $this->assertEquals('已检验', DeliveryStatus::INSPECTED->getLabel());
        $this->assertEquals('已入库', DeliveryStatus::WAREHOUSED->getLabel());
    }

    public function testGetItem(): void
    {
        $items = DeliveryStatus::getItem();

        $expectedItems = [
            'pending' => '待发货',
            'shipped' => '已发货',
            'in_transit' => '运输中',
            'arrived' => '已到达',
            'received' => '已收货',
            'inspected' => '已检验',
            'warehoused' => '已入库',
        ];

        $this->assertEquals($expectedItems, $items);
        $this->assertCount(7, $items);
    }

    public function testAllCases(): void
    {
        $cases = DeliveryStatus::cases();
        $this->assertCount(7, $cases);

        $values = array_map(fn (DeliveryStatus $case) => $case->value, $cases);
        $this->assertContains('pending', $values);
        $this->assertContains('shipped', $values);
        $this->assertContains('in_transit', $values);
        $this->assertContains('arrived', $values);
        $this->assertContains('received', $values);
        $this->assertContains('inspected', $values);
        $this->assertContains('warehoused', $values);
    }

    public function testFromValue(): void
    {
        $this->assertEquals(DeliveryStatus::PENDING, DeliveryStatus::from('pending'));
        $this->assertEquals(DeliveryStatus::SHIPPED, DeliveryStatus::from('shipped'));
        $this->assertEquals(DeliveryStatus::IN_TRANSIT, DeliveryStatus::from('in_transit'));
        $this->assertEquals(DeliveryStatus::ARRIVED, DeliveryStatus::from('arrived'));
        $this->assertEquals(DeliveryStatus::RECEIVED, DeliveryStatus::from('received'));
        $this->assertEquals(DeliveryStatus::INSPECTED, DeliveryStatus::from('inspected'));
        $this->assertEquals(DeliveryStatus::WAREHOUSED, DeliveryStatus::from('warehoused'));
    }

    public function testToArray(): void
    {
        $array = DeliveryStatus::PENDING->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('value', $array);
        $this->assertEquals('待发货', $array['label']);
        $this->assertEquals('pending', $array['value']);

        $array = DeliveryStatus::SHIPPED->toArray();
        $this->assertEquals('已发货', $array['label']);
        $this->assertEquals('shipped', $array['value']);

        $array = DeliveryStatus::IN_TRANSIT->toArray();
        $this->assertEquals('运输中', $array['label']);
        $this->assertEquals('in_transit', $array['value']);

        $array = DeliveryStatus::ARRIVED->toArray();
        $this->assertEquals('已到达', $array['label']);
        $this->assertEquals('arrived', $array['value']);

        $array = DeliveryStatus::RECEIVED->toArray();
        $this->assertEquals('已收货', $array['label']);
        $this->assertEquals('received', $array['value']);

        $array = DeliveryStatus::INSPECTED->toArray();
        $this->assertEquals('已检验', $array['label']);
        $this->assertEquals('inspected', $array['value']);

        $array = DeliveryStatus::WAREHOUSED->toArray();
        $this->assertEquals('已入库', $array['label']);
        $this->assertEquals('warehoused', $array['value']);
    }
}
