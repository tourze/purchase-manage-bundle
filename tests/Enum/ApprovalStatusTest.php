<?php

namespace Tourze\PurchaseManageBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\PurchaseManageBundle\Enum\ApprovalStatus;

/**
 * @internal
 */
#[CoversClass(ApprovalStatus::class)]
class ApprovalStatusTest extends AbstractEnumTestCase
{
    public function testEnumValues(): void
    {
        $this->assertEquals('pending', ApprovalStatus::PENDING->value);
        $this->assertEquals('approved', ApprovalStatus::APPROVED->value);
        $this->assertEquals('rejected', ApprovalStatus::REJECTED->value);
        $this->assertEquals('cancelled', ApprovalStatus::CANCELLED->value);
    }

    public function testLabels(): void
    {
        $this->assertEquals('待审批', ApprovalStatus::PENDING->getLabel());
        $this->assertEquals('已批准', ApprovalStatus::APPROVED->getLabel());
        $this->assertEquals('已拒绝', ApprovalStatus::REJECTED->getLabel());
        $this->assertEquals('已取消', ApprovalStatus::CANCELLED->getLabel());
    }

    public function testGetItem(): void
    {
        $items = ApprovalStatus::getItem();

        $expectedItems = [
            'pending' => '待审批',
            'approved' => '已批准',
            'rejected' => '已拒绝',
            'cancelled' => '已取消',
        ];

        $this->assertEquals($expectedItems, $items);
        $this->assertCount(4, $items);
    }

    public function testAllCases(): void
    {
        $cases = ApprovalStatus::cases();
        $this->assertCount(4, $cases);

        $values = array_map(fn (ApprovalStatus $case) => $case->value, $cases);
        $this->assertContains('pending', $values);
        $this->assertContains('approved', $values);
        $this->assertContains('rejected', $values);
        $this->assertContains('cancelled', $values);
    }

    public function testFromValue(): void
    {
        $this->assertEquals(ApprovalStatus::PENDING, ApprovalStatus::from('pending'));
        $this->assertEquals(ApprovalStatus::APPROVED, ApprovalStatus::from('approved'));
        $this->assertEquals(ApprovalStatus::REJECTED, ApprovalStatus::from('rejected'));
        $this->assertEquals(ApprovalStatus::CANCELLED, ApprovalStatus::from('cancelled'));
    }

    public function testToArray(): void
    {
        $array = ApprovalStatus::PENDING->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('value', $array);
        $this->assertEquals('待审批', $array['label']);
        $this->assertEquals('pending', $array['value']);

        $array = ApprovalStatus::APPROVED->toArray();
        $this->assertEquals('已批准', $array['label']);
        $this->assertEquals('approved', $array['value']);

        $array = ApprovalStatus::REJECTED->toArray();
        $this->assertEquals('已拒绝', $array['label']);
        $this->assertEquals('rejected', $array['value']);

        $array = ApprovalStatus::CANCELLED->toArray();
        $this->assertEquals('已取消', $array['label']);
        $this->assertEquals('cancelled', $array['value']);
    }
}
