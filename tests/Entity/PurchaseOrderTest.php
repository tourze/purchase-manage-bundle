<?php

namespace Tourze\PurchaseManageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrderItem;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;
use Tourze\SupplierManageBundle\Entity\Supplier;

/**
 * @internal
 */
#[CoversClass(PurchaseOrder::class)]
class PurchaseOrderTest extends AbstractEntityTestCase
{
    private PurchaseOrder $order;

    public function testSetAndGetOrderNumber(): void
    {
        $orderNumber = 'PO-2024-001';
        $this->order->setOrderNumber($orderNumber);
        $this->assertEquals($orderNumber, $this->order->getOrderNumber());
    }

    public function testSetAndGetTitle(): void
    {
        $title = 'Purchase Order Title';
        $this->order->setTitle($title);
        $this->assertEquals($title, $this->order->getTitle());
    }

    public function testSetAndGetSupplier(): void
    {
        $supplier = new Supplier();
        $supplier->setName('Test Supplier');

        $this->order->setSupplier($supplier);
        $this->assertSame($supplier, $this->order->getSupplier());
    }

    public function testSetAndGetStatus(): void
    {
        $status = PurchaseOrderStatus::APPROVED;
        $this->order->setStatus($status);
        $this->assertEquals($status, $this->order->getStatus());
    }

    public function testAddAndRemoveItem(): void
    {
        $item = new PurchaseOrderItem();
        $item->setProductName('Test Product');

        $this->order->addItem($item);
        $this->assertCount(1, $this->order->getItems());
        $this->assertTrue($this->order->getItems()->contains($item));
        $this->assertSame($this->order, $item->getPurchaseOrder());

        $this->order->removeItem($item);
        $this->assertCount(0, $this->order->getItems());
        $this->assertFalse($this->order->getItems()->contains($item));
    }

    public function testCalculateTotalAmount(): void
    {
        $item1 = new PurchaseOrderItem();
        $item1->setQuantity('10');
        $item1->setUnitPrice('100');
        $item1->setTaxRate('10.00');
        $item1->calculateSubtotal();

        $item2 = new PurchaseOrderItem();
        $item2->setQuantity('5');
        $item2->setUnitPrice('200');
        $item2->setTaxRate('5.00');
        $item2->calculateSubtotal();

        $this->order->addItem($item1);
        $this->order->addItem($item2);
        $this->order->calculateTotalAmount();

        $expectedAmount = (10 * 100) + (5 * 200);
        $this->assertEquals(number_format($expectedAmount, 2, '.', ''), $this->order->getTotalAmount());
    }

    public function testSetAndGetExpectedDeliveryDate(): void
    {
        $date = new \DateTimeImmutable('2024-02-01');
        $this->order->setExpectedDeliveryDate($date);
        $this->assertEquals($date, $this->order->getExpectedDeliveryDate());
    }

    public function testSetAndGetActualDeliveryDate(): void
    {
        $date = new \DateTimeImmutable('2024-02-05');
        $this->order->setActualDeliveryDate($date);
        $this->assertEquals($date, $this->order->getActualDeliveryDate());
    }

    public function testSetAndGetDeliveryAddress(): void
    {
        $address = '123 Delivery St';
        $this->order->setDeliveryAddress($address);
        $this->assertEquals($address, $this->order->getDeliveryAddress());
    }

    public function testSetAndGetCurrency(): void
    {
        $currency = 'USD';
        $this->order->setCurrency($currency);
        $this->assertEquals($currency, $this->order->getCurrency());
    }

    public function testSetAndGetRemark(): void
    {
        $remark = 'Test remark';
        $this->order->setRemark($remark);
        $this->assertEquals($remark, $this->order->getRemark());
    }

    public function testDefaultValues(): void
    {
        $this->assertEquals(PurchaseOrderStatus::DRAFT, $this->order->getStatus());
        $this->assertEquals('0.00', $this->order->getTotalAmount());
        $this->assertEquals('0.00', $this->order->getTaxAmount());
        $this->assertEquals('0.00', $this->order->getDiscountAmount());
        $this->assertEquals('CNY', $this->order->getCurrency());
        $this->assertCount(0, $this->order->getItems());
    }

    protected function createEntity(): object
    {
        return new PurchaseOrder();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = new PurchaseOrder();
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'orderNumber' => ['orderNumber', 'PO-2024-001'],
            'title' => ['title', 'Purchase Order Title'],
            'status' => ['status', PurchaseOrderStatus::APPROVED],
            'totalAmount' => ['totalAmount', '1500.00'],
            'taxAmount' => ['taxAmount', '150.00'],
            'discountAmount' => ['discountAmount', '50.00'],
            'expectedDeliveryDate' => ['expectedDeliveryDate', new \DateTimeImmutable('2024-02-01')],
            'actualDeliveryDate' => ['actualDeliveryDate', new \DateTimeImmutable('2024-01-28')],
            'deliveryAddress' => ['deliveryAddress', '123 Delivery St'],
            'currency' => ['currency', 'USD'],
            'remark' => ['remark', 'Test remark'],
        ];
    }
}
