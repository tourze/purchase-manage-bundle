<?php

namespace Tourze\PurchaseManageBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;

/**
 * @internal
 */
#[CoversClass(PurchaseOrderStatus::class)]
class PurchaseOrderStatusTest extends AbstractEnumTestCase
{
    public function testEnumValues(): void
    {
        $this->assertEquals('draft', PurchaseOrderStatus::DRAFT->value);
        $this->assertEquals('pending_approval', PurchaseOrderStatus::PENDING_APPROVAL->value);
        $this->assertEquals('approved', PurchaseOrderStatus::APPROVED->value);
        $this->assertEquals('purchasing', PurchaseOrderStatus::PURCHASING->value);
        $this->assertEquals('shipped', PurchaseOrderStatus::SHIPPED->value);
        $this->assertEquals('received', PurchaseOrderStatus::RECEIVED->value);
        $this->assertEquals('completed', PurchaseOrderStatus::COMPLETED->value);
        $this->assertEquals('cancelled', PurchaseOrderStatus::CANCELLED->value);
        $this->assertEquals('rejected', PurchaseOrderStatus::REJECTED->value);
    }

    public function testLabels(): void
    {
        $this->assertEquals('草稿', PurchaseOrderStatus::DRAFT->getLabel());
        $this->assertEquals('待审批', PurchaseOrderStatus::PENDING_APPROVAL->getLabel());
        $this->assertEquals('已审批', PurchaseOrderStatus::APPROVED->getLabel());
        $this->assertEquals('采购中', PurchaseOrderStatus::PURCHASING->getLabel());
        $this->assertEquals('已发货', PurchaseOrderStatus::SHIPPED->getLabel());
        $this->assertEquals('已收货', PurchaseOrderStatus::RECEIVED->getLabel());
        $this->assertEquals('已完成', PurchaseOrderStatus::COMPLETED->getLabel());
        $this->assertEquals('已取消', PurchaseOrderStatus::CANCELLED->getLabel());
        $this->assertEquals('已拒绝', PurchaseOrderStatus::REJECTED->getLabel());
    }

    public function testGetItem(): void
    {
        $items = PurchaseOrderStatus::getItem();

        $expectedItems = [
            'draft' => '草稿',
            'pending_approval' => '待审批',
            'approved' => '已审批',
            'purchasing' => '采购中',
            'shipped' => '已发货',
            'received' => '已收货',
            'completed' => '已完成',
            'cancelled' => '已取消',
            'rejected' => '已拒绝',
        ];

        $this->assertEquals($expectedItems, $items);
        $this->assertCount(9, $items);
    }

    public function testAllCases(): void
    {
        $cases = PurchaseOrderStatus::cases();
        $this->assertCount(9, $cases);

        $values = array_map(fn (PurchaseOrderStatus $case) => $case->value, $cases);
        $this->assertContains('draft', $values);
        $this->assertContains('pending_approval', $values);
        $this->assertContains('approved', $values);
        $this->assertContains('purchasing', $values);
        $this->assertContains('shipped', $values);
        $this->assertContains('received', $values);
        $this->assertContains('completed', $values);
        $this->assertContains('cancelled', $values);
        $this->assertContains('rejected', $values);
    }

    public function testFromValue(): void
    {
        $this->assertEquals(PurchaseOrderStatus::DRAFT, PurchaseOrderStatus::from('draft'));
        $this->assertEquals(PurchaseOrderStatus::PENDING_APPROVAL, PurchaseOrderStatus::from('pending_approval'));
        $this->assertEquals(PurchaseOrderStatus::APPROVED, PurchaseOrderStatus::from('approved'));
        $this->assertEquals(PurchaseOrderStatus::PURCHASING, PurchaseOrderStatus::from('purchasing'));
        $this->assertEquals(PurchaseOrderStatus::SHIPPED, PurchaseOrderStatus::from('shipped'));
        $this->assertEquals(PurchaseOrderStatus::RECEIVED, PurchaseOrderStatus::from('received'));
        $this->assertEquals(PurchaseOrderStatus::COMPLETED, PurchaseOrderStatus::from('completed'));
        $this->assertEquals(PurchaseOrderStatus::CANCELLED, PurchaseOrderStatus::from('cancelled'));
        $this->assertEquals(PurchaseOrderStatus::REJECTED, PurchaseOrderStatus::from('rejected'));
    }

    public function testToArray(): void
    {
        $array = PurchaseOrderStatus::DRAFT->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('value', $array);
        $this->assertEquals('草稿', $array['label']);
        $this->assertEquals('draft', $array['value']);

        $array = PurchaseOrderStatus::PENDING_APPROVAL->toArray();
        $this->assertEquals('待审批', $array['label']);
        $this->assertEquals('pending_approval', $array['value']);

        $array = PurchaseOrderStatus::APPROVED->toArray();
        $this->assertEquals('已审批', $array['label']);
        $this->assertEquals('approved', $array['value']);

        $array = PurchaseOrderStatus::PURCHASING->toArray();
        $this->assertEquals('采购中', $array['label']);
        $this->assertEquals('purchasing', $array['value']);

        $array = PurchaseOrderStatus::SHIPPED->toArray();
        $this->assertEquals('已发货', $array['label']);
        $this->assertEquals('shipped', $array['value']);

        $array = PurchaseOrderStatus::RECEIVED->toArray();
        $this->assertEquals('已收货', $array['label']);
        $this->assertEquals('received', $array['value']);

        $array = PurchaseOrderStatus::COMPLETED->toArray();
        $this->assertEquals('已完成', $array['label']);
        $this->assertEquals('completed', $array['value']);

        $array = PurchaseOrderStatus::CANCELLED->toArray();
        $this->assertEquals('已取消', $array['label']);
        $this->assertEquals('cancelled', $array['value']);

        $array = PurchaseOrderStatus::REJECTED->toArray();
        $this->assertEquals('已拒绝', $array['label']);
        $this->assertEquals('rejected', $array['value']);
    }
}
