<?php

namespace Tourze\PurchaseManageBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;
use Tourze\PurchaseManageBundle\Event\PurchaseOrderStatusChangedEvent;

/**
 * @internal
 */
#[CoversClass(PurchaseOrderStatusChangedEvent::class)]
class PurchaseOrderStatusChangedEventTest extends AbstractEventTestCase
{
    public function testConstruct(): void
    {
        $purchaseOrder = new PurchaseOrder();
        $oldStatus = PurchaseOrderStatus::DRAFT;
        $newStatus = PurchaseOrderStatus::PENDING_APPROVAL;

        $event = new PurchaseOrderStatusChangedEvent($purchaseOrder, $oldStatus, $newStatus);

        $this->assertSame($purchaseOrder, $event->getPurchaseOrder());
        $this->assertSame($oldStatus, $event->getOldStatus());
        $this->assertSame($newStatus, $event->getNewStatus());
    }

    public function testGetPurchaseOrder(): void
    {
        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder->setOrderNumber('PO-2024-001');

        $event = new PurchaseOrderStatusChangedEvent(
            $purchaseOrder,
            PurchaseOrderStatus::DRAFT,
            PurchaseOrderStatus::APPROVED
        );

        $retrievedOrder = $event->getPurchaseOrder();
        $this->assertSame($purchaseOrder, $retrievedOrder);
        $this->assertEquals('PO-2024-001', $retrievedOrder->getOrderNumber());
    }

    public function testGetOldStatus(): void
    {
        $purchaseOrder = new PurchaseOrder();
        $oldStatus = PurchaseOrderStatus::PENDING_APPROVAL;
        $newStatus = PurchaseOrderStatus::APPROVED;

        $event = new PurchaseOrderStatusChangedEvent($purchaseOrder, $oldStatus, $newStatus);

        $this->assertSame($oldStatus, $event->getOldStatus());
        $this->assertEquals(PurchaseOrderStatus::PENDING_APPROVAL, $event->getOldStatus());
    }

    public function testGetNewStatus(): void
    {
        $purchaseOrder = new PurchaseOrder();
        $oldStatus = PurchaseOrderStatus::APPROVED;
        $newStatus = PurchaseOrderStatus::PURCHASING;

        $event = new PurchaseOrderStatusChangedEvent($purchaseOrder, $oldStatus, $newStatus);

        $this->assertSame($newStatus, $event->getNewStatus());
        $this->assertEquals(PurchaseOrderStatus::PURCHASING, $event->getNewStatus());
    }

    public function testEventName(): void
    {
        $this->assertEquals('purchase_order.status_changed', PurchaseOrderStatusChangedEvent::NAME);
    }

    public function testStatusTransitions(): void
    {
        $purchaseOrder = new PurchaseOrder();

        // 测试从草稿到待审批
        $event1 = new PurchaseOrderStatusChangedEvent(
            $purchaseOrder,
            PurchaseOrderStatus::DRAFT,
            PurchaseOrderStatus::PENDING_APPROVAL
        );
        $this->assertEquals(PurchaseOrderStatus::DRAFT, $event1->getOldStatus());
        $this->assertEquals(PurchaseOrderStatus::PENDING_APPROVAL, $event1->getNewStatus());

        // 测试从待审批到已审批
        $event2 = new PurchaseOrderStatusChangedEvent(
            $purchaseOrder,
            PurchaseOrderStatus::PENDING_APPROVAL,
            PurchaseOrderStatus::APPROVED
        );
        $this->assertEquals(PurchaseOrderStatus::PENDING_APPROVAL, $event2->getOldStatus());
        $this->assertEquals(PurchaseOrderStatus::APPROVED, $event2->getNewStatus());

        // 测试从已审批到采购中
        $event3 = new PurchaseOrderStatusChangedEvent(
            $purchaseOrder,
            PurchaseOrderStatus::APPROVED,
            PurchaseOrderStatus::PURCHASING
        );
        $this->assertEquals(PurchaseOrderStatus::APPROVED, $event3->getOldStatus());
        $this->assertEquals(PurchaseOrderStatus::PURCHASING, $event3->getNewStatus());
    }

    public function testRejectionTransition(): void
    {
        $purchaseOrder = new PurchaseOrder();

        $event = new PurchaseOrderStatusChangedEvent(
            $purchaseOrder,
            PurchaseOrderStatus::PENDING_APPROVAL,
            PurchaseOrderStatus::REJECTED
        );

        $this->assertEquals(PurchaseOrderStatus::PENDING_APPROVAL, $event->getOldStatus());
        $this->assertEquals(PurchaseOrderStatus::REJECTED, $event->getNewStatus());
    }

    public function testCancellationTransition(): void
    {
        $purchaseOrder = new PurchaseOrder();

        $event = new PurchaseOrderStatusChangedEvent(
            $purchaseOrder,
            PurchaseOrderStatus::APPROVED,
            PurchaseOrderStatus::CANCELLED
        );

        $this->assertEquals(PurchaseOrderStatus::APPROVED, $event->getOldStatus());
        $this->assertEquals(PurchaseOrderStatus::CANCELLED, $event->getNewStatus());
    }

    public function testCompletionTransition(): void
    {
        $purchaseOrder = new PurchaseOrder();

        $event = new PurchaseOrderStatusChangedEvent(
            $purchaseOrder,
            PurchaseOrderStatus::RECEIVED,
            PurchaseOrderStatus::COMPLETED
        );

        $this->assertEquals(PurchaseOrderStatus::RECEIVED, $event->getOldStatus());
        $this->assertEquals(PurchaseOrderStatus::COMPLETED, $event->getNewStatus());
    }
}
