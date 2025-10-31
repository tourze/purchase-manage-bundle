<?php

namespace Tourze\PurchaseManageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseApproval;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\ApprovalStatus;

/**
 * @internal
 */
#[CoversClass(PurchaseApproval::class)]
class PurchaseApprovalTest extends AbstractEntityTestCase
{
    private PurchaseApproval $approval;

    public function testSetAndGetPurchaseOrder(): void
    {
        $order = new PurchaseOrder();
        $this->approval->setPurchaseOrder($order);
        $this->assertSame($order, $this->approval->getPurchaseOrder());
    }

    public function testSetAndGetLevel(): void
    {
        $level = '部门经理审批';
        $this->approval->setLevel($level);
        $this->assertEquals($level, $this->approval->getLevel());
    }

    public function testSetAndGetSequence(): void
    {
        $sequence = 2;
        $this->approval->setSequence($sequence);
        $this->assertEquals($sequence, $this->approval->getSequence());
    }

    public function testSetAndGetStatus(): void
    {
        $status = ApprovalStatus::APPROVED;
        $this->approval->setStatus($status);
        $this->assertEquals($status, $this->approval->getStatus());
    }

    public function testSetAndGetApproverRole(): void
    {
        $role = 'ROLE_MANAGER';
        $this->approval->setApproverRole($role);
        $this->assertEquals($role, $this->approval->getApproverRole());
    }

    public function testSetAndGetApproverId(): void
    {
        $approverId = 123;
        $this->approval->setApproverId($approverId);
        $this->assertEquals($approverId, $this->approval->getApproverId());
    }

    public function testSetAndGetComment(): void
    {
        $comment = 'Approved with conditions';
        $this->approval->setComment($comment);
        $this->assertEquals($comment, $this->approval->getComment());
    }

    public function testSetAndGetApprovedAt(): void
    {
        $date = new \DateTimeImmutable('2024-01-15 10:30:00');
        $this->approval->setApproveTime($date);
        $this->assertEquals($date, $this->approval->getApproveTime());
    }

    public function testSetAndGetAmountLimit(): void
    {
        $limit = '50000';
        $this->approval->setAmountLimit($limit);
        $this->assertEquals($limit, $this->approval->getAmountLimit());
    }

    public function testSetAndGetAttachments(): void
    {
        $attachments = ['approval1' => 'approval1.pdf', 'approval2' => 'approval2.doc'];
        $this->approval->setAttachments($attachments);
        $this->assertEquals($attachments, $this->approval->getAttachments());
    }

    public function testSetAndGetRequireCountersign(): void
    {
        $this->approval->setRequireCountersign(true);
        $this->assertTrue($this->approval->isRequireCountersign());

        $this->approval->setRequireCountersign(false);
        $this->assertFalse($this->approval->isRequireCountersign());
    }

    public function testSetAndGetRemark(): void
    {
        $remark = 'Test remark';
        $this->approval->setRemark($remark);
        $this->assertEquals($remark, $this->approval->getRemark());
    }

    public function testDefaultValues(): void
    {
        $this->assertEquals(ApprovalStatus::PENDING, $this->approval->getStatus());
        $this->assertEquals(1, $this->approval->getSequence());
        $this->assertFalse($this->approval->isRequireCountersign());
        $this->assertNull($this->approval->getApproverId());
        $this->assertNull($this->approval->getApproveTime());
    }

    protected function createEntity(): object
    {
        return new PurchaseApproval();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->approval = new PurchaseApproval();
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'level' => ['level', '部门经理审批'],
            'sequence' => ['sequence', 2],
            'status' => ['status', ApprovalStatus::APPROVED],
            'approverRole' => ['approverRole', 'ROLE_MANAGER'],
            'approverId' => ['approverId', 123],
            'comment' => ['comment', 'Approved with conditions'],
            'amountLimit' => ['amountLimit', '50000'],
            'attachments' => ['attachments', ['approval1' => 'approval1.pdf', 'approval2' => 'approval2.doc']],
            'requireCountersign' => ['requireCountersign', true],
            'remark' => ['remark', 'Test remark'],
        ];
    }
}
