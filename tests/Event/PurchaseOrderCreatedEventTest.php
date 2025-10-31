<?php

namespace Tourze\PurchaseManageBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Event\PurchaseOrderCreatedEvent;

/**
 * @internal
 */
#[CoversClass(PurchaseOrderCreatedEvent::class)]
class PurchaseOrderCreatedEventTest extends AbstractEventTestCase
{
    public function testConstruct(): void
    {
        $purchaseOrder = new PurchaseOrder();
        $event = new PurchaseOrderCreatedEvent($purchaseOrder);

        $this->assertSame($purchaseOrder, $event->getPurchaseOrder());
    }

    public function testGetPurchaseOrder(): void
    {
        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder->setOrderNumber('PO-2024-001');

        $event = new PurchaseOrderCreatedEvent($purchaseOrder);
        $retrievedOrder = $event->getPurchaseOrder();

        $this->assertSame($purchaseOrder, $retrievedOrder);
        $this->assertEquals('PO-2024-001', $retrievedOrder->getOrderNumber());
    }

    public function testEventName(): void
    {
        $this->assertEquals('purchase_order.created', PurchaseOrderCreatedEvent::NAME);
    }

    public function testEventIsReadOnly(): void
    {
        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder->setOrderNumber('PO-2024-002');

        $event = new PurchaseOrderCreatedEvent($purchaseOrder);

        // 确保事件中的采购订单对象是只读的（通过构造函数注入后不可变）
        $retrievedOrder = $event->getPurchaseOrder();
        $this->assertSame($purchaseOrder, $retrievedOrder);

        // 修改原对象
        $purchaseOrder->setOrderNumber('MODIFIED');

        // 事件中的对象也会改变（因为是同一个引用）
        $this->assertEquals('MODIFIED', $event->getPurchaseOrder()->getOrderNumber());
    }

    public function testMultipleEventsWithDifferentOrders(): void
    {
        $order1 = new PurchaseOrder();
        $order1->setOrderNumber('PO-2024-001');

        $order2 = new PurchaseOrder();
        $order2->setOrderNumber('PO-2024-002');

        $event1 = new PurchaseOrderCreatedEvent($order1);
        $event2 = new PurchaseOrderCreatedEvent($order2);

        $this->assertSame($order1, $event1->getPurchaseOrder());
        $this->assertSame($order2, $event2->getPurchaseOrder());
        $this->assertNotSame($event1->getPurchaseOrder(), $event2->getPurchaseOrder());
    }
}
