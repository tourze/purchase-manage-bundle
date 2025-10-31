<?php

namespace Tourze\PurchaseManageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrderItem;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;

/**
 * @internal
 */
#[CoversClass(PurchaseOrderItem::class)]
class PurchaseOrderItemTest extends AbstractEntityTestCase
{
    private PurchaseOrderItem $item;

    public function testSetAndGetPurchaseOrder(): void
    {
        $order = new PurchaseOrder();
        $this->item->setPurchaseOrder($order);
        $this->assertSame($order, $this->item->getPurchaseOrder());
    }

    public function testSetAndGetProductName(): void
    {
        $name = 'Test Product';
        $this->item->setProductName($name);
        $this->assertEquals($name, $this->item->getProductName());
    }

    public function testSetAndGetProductCode(): void
    {
        $code = 'PROD-001';
        $this->item->setProductCode($code);
        $this->assertEquals($code, $this->item->getProductCode());
    }

    public function testSetAndGetSpecification(): void
    {
        $spec = 'Size: Large, Color: Blue';
        $this->item->setSpecification($spec);
        $this->assertEquals($spec, $this->item->getSpecification());
    }

    public function testSetAndGetUnit(): void
    {
        $unit = 'piece';
        $this->item->setUnit($unit);
        $this->assertEquals($unit, $this->item->getUnit());
    }

    public function testSetAndGetQuantity(): void
    {
        $quantity = '100';
        $this->item->setQuantity($quantity);
        $this->assertEquals($quantity, $this->item->getQuantity());
    }

    public function testSetAndGetUnitPrice(): void
    {
        $price = '99.99';
        $this->item->setUnitPrice($price);
        $this->assertEquals($price, $this->item->getUnitPrice());
    }

    public function testSetAndGetTaxRate(): void
    {
        $rate = '0.13';
        $this->item->setTaxRate($rate);
        $this->assertEquals($rate, $this->item->getTaxRate());
    }

    public function testCalculateSubtotal(): void
    {
        $this->item->setQuantity('10');
        $this->item->setUnitPrice('100');
        $this->item->setTaxRate('10.00');

        $this->item->calculateSubtotal();

        $expectedSubtotal = 10 * 100;
        $expectedTax = $expectedSubtotal * 0.1;
        $expectedTotal = $expectedSubtotal + $expectedTax;

        $this->assertEquals(number_format($expectedSubtotal, 2, '.', ''), $this->item->getSubtotal());
        $this->assertEquals(number_format($expectedTax, 2, '.', ''), $this->item->getTaxAmount());
    }

    public function testSetAndGetExpectedDeliveryDate(): void
    {
        $date = new \DateTimeImmutable('2024-03-01');
        $this->item->setExpectedDeliveryDate($date);
        $this->assertEquals($date, $this->item->getExpectedDeliveryDate());
    }

    public function testSetAndGetDeliveryStatus(): void
    {
        $status = DeliveryStatus::SHIPPED;
        $this->item->setDeliveryStatus($status);
        $this->assertEquals($status, $this->item->getDeliveryStatus());
    }

    public function testSetAndGetReceivedQuantity(): void
    {
        $quantity = '95';
        $this->item->setReceivedQuantity($quantity);
        $this->assertEquals($quantity, $this->item->getReceivedQuantity());
    }

    public function testSetAndGetQualifiedQuantity(): void
    {
        $quantity = '90';
        $this->item->setQualifiedQuantity($quantity);
        $this->assertEquals($quantity, $this->item->getQualifiedQuantity());
    }

    public function testSetAndGetRemark(): void
    {
        $remark = 'Test remark';
        $this->item->setRemark($remark);
        $this->assertEquals($remark, $this->item->getRemark());
    }

    public function testDefaultValues(): void
    {
        $this->assertEquals('1.0000', $this->item->getQuantity());
        $this->assertEquals('0.0000', $this->item->getUnitPrice());
        $this->assertEquals('0.00', $this->item->getTaxRate());
        $this->assertEquals('0.00', $this->item->getSubtotal());
        $this->assertEquals('0.00', $this->item->getTaxAmount());
        $this->assertEquals('0.0000', $this->item->getReceivedQuantity());
        $this->assertEquals('0.0000', $this->item->getQualifiedQuantity());
        $this->assertEquals(DeliveryStatus::PENDING, $this->item->getDeliveryStatus());
    }

    protected function createEntity(): object
    {
        return new PurchaseOrderItem();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->item = new PurchaseOrderItem();
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'productName' => ['productName', 'Test Product'],
            'productCode' => ['productCode', 'PROD-001'],
            'specification' => ['specification', 'Size: Large, Color: Blue'],
            'unit' => ['unit', 'piece'],
            'quantity' => ['quantity', '100'],
            'unitPrice' => ['unitPrice', '99.99'],
            'taxRate' => ['taxRate', '0.13'],
            'subtotal' => ['subtotal', '1130.00'],
            'taxAmount' => ['taxAmount', '130.00'],
            'receivedQuantity' => ['receivedQuantity', '95'],
            'qualifiedQuantity' => ['qualifiedQuantity', '90'],
            'deliveryStatus' => ['deliveryStatus', DeliveryStatus::SHIPPED],
            'remark' => ['remark', 'Test remark'],
        ];
    }
}
