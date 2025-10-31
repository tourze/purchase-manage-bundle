<?php

namespace Tourze\PurchaseManageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;

/**
 * @internal
 */
#[CoversClass(PurchaseDelivery::class)]
class PurchaseDeliveryTest extends AbstractEntityTestCase
{
    private PurchaseDelivery $delivery;

    public function testSetAndGetPurchaseOrder(): void
    {
        $order = new PurchaseOrder();
        $this->delivery->setPurchaseOrder($order);
        $this->assertSame($order, $this->delivery->getPurchaseOrder());
    }

    public function testSetAndGetBatchNumber(): void
    {
        $batchNumber = 'BATCH-2024-001';
        $this->delivery->setBatchNumber($batchNumber);
        $this->assertEquals($batchNumber, $this->delivery->getBatchNumber());
    }

    public function testSetAndGetStatus(): void
    {
        $status = DeliveryStatus::SHIPPED;
        $this->delivery->setStatus($status);
        $this->assertEquals($status, $this->delivery->getStatus());
    }

    public function testSetAndGetLogisticsCompany(): void
    {
        $company = 'SF Express';
        $this->delivery->setLogisticsCompany($company);
        $this->assertEquals($company, $this->delivery->getLogisticsCompany());
    }

    public function testSetAndGetTrackingNumber(): void
    {
        $trackingNumber = 'SF1234567890';
        $this->delivery->setTrackingNumber($trackingNumber);
        $this->assertEquals($trackingNumber, $this->delivery->getTrackingNumber());
    }

    public function testSetAndGetShippedAt(): void
    {
        $date = new \DateTimeImmutable('2024-01-10');
        $this->delivery->setShipTime($date);
        $this->assertEquals($date, $this->delivery->getShipTime());
    }

    public function testSetAndGetEstimatedArrivalAt(): void
    {
        $date = new \DateTimeImmutable('2024-01-15');
        $this->delivery->setEstimatedArrivalTime($date);
        $this->assertEquals($date, $this->delivery->getEstimatedArrivalTime());
    }

    public function testSetAndGetActualArrivalAt(): void
    {
        $date = new \DateTimeImmutable('2024-01-14');
        $this->delivery->setActualArrivalTime($date);
        $this->assertEquals($date, $this->delivery->getActualArrivalTime());
    }

    public function testSetAndGetReceivedAt(): void
    {
        $date = new \DateTimeImmutable('2024-01-14 15:30:00');
        $this->delivery->setReceiveTime($date);
        $this->assertEquals($date, $this->delivery->getReceiveTime());
    }

    public function testSetAndGetReceivedBy(): void
    {
        $receivedBy = 'John Doe';
        $this->delivery->setReceivedBy($receivedBy);
        $this->assertEquals($receivedBy, $this->delivery->getReceivedBy());
    }

    public function testSetAndGetDeliveredQuantity(): void
    {
        $quantity = '100';
        $this->delivery->setDeliveredQuantity($quantity);
        $this->assertEquals($quantity, $this->delivery->getDeliveredQuantity());
    }

    public function testSetAndGetInspectedAt(): void
    {
        $date = new \DateTimeImmutable('2024-01-15 10:00:00');
        $this->delivery->setInspectTime($date);
        $this->assertEquals($date, $this->delivery->getInspectTime());
    }

    public function testSetAndGetInspectedBy(): void
    {
        $inspectedBy = 'Jane Smith';
        $this->delivery->setInspectedBy($inspectedBy);
        $this->assertEquals($inspectedBy, $this->delivery->getInspectedBy());
    }

    public function testSetAndGetInspectionPassed(): void
    {
        $this->delivery->setInspectionPassed(true);
        $this->assertTrue($this->delivery->isInspectionPassed());

        $this->delivery->setInspectionPassed(false);
        $this->assertFalse($this->delivery->isInspectionPassed());
    }

    public function testSetAndGetQualifiedQuantity(): void
    {
        $quantity = '95';
        $this->delivery->setQualifiedQuantity($quantity);
        $this->assertEquals($quantity, $this->delivery->getQualifiedQuantity());
    }

    public function testSetAndGetRejectedQuantity(): void
    {
        $quantity = '5';
        $this->delivery->setRejectedQuantity($quantity);
        $this->assertEquals($quantity, $this->delivery->getRejectedQuantity());
    }

    public function testSetAndGetInspectionComment(): void
    {
        $comment = '5 items damaged during shipping';
        $this->delivery->setInspectionComment($comment);
        $this->assertEquals($comment, $this->delivery->getInspectionComment());
    }

    public function testSetAndGetWarehousedAt(): void
    {
        $date = new \DateTimeImmutable('2024-01-16 09:00:00');
        $this->delivery->setWarehouseTime($date);
        $this->assertEquals($date, $this->delivery->getWarehouseTime());
    }

    public function testSetAndGetWarehousedBy(): void
    {
        $warehousedBy = 'Bob Johnson';
        $this->delivery->setWarehousedBy($warehousedBy);
        $this->assertEquals($warehousedBy, $this->delivery->getWarehousedBy());
    }

    public function testSetAndGetWarehouseLocation(): void
    {
        $location = 'A-12-3';
        $this->delivery->setWarehouseLocation($location);
        $this->assertEquals($location, $this->delivery->getWarehouseLocation());
    }

    public function testSetAndGetDiscrepancyReason(): void
    {
        $reason = 'Quantity mismatch';
        $this->delivery->setDiscrepancyReason($reason);
        $this->assertEquals($reason, $this->delivery->getDiscrepancyReason());
    }

    public function testSetAndGetAttachments(): void
    {
        $attachments = ['delivery_note' => 'delivery_note.pdf', 'inspection_report' => 'inspection_report.doc'];
        $this->delivery->setAttachments($attachments);
        $this->assertEquals($attachments, $this->delivery->getAttachments());
    }

    public function testDefaultValues(): void
    {
        $this->assertEquals(DeliveryStatus::PENDING, $this->delivery->getStatus());
        $this->assertEquals('0.0000', $this->delivery->getDeliveredQuantity());
        $this->assertEquals('0.0000', $this->delivery->getQualifiedQuantity());
        $this->assertEquals('0.0000', $this->delivery->getRejectedQuantity());
        $this->assertFalse($this->delivery->isInspectionPassed());
    }

    protected function createEntity(): object
    {
        return new PurchaseDelivery();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->delivery = new PurchaseDelivery();
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'batchNumber' => ['batchNumber', 'BATCH-2024-001'],
            'status' => ['status', DeliveryStatus::SHIPPED],
            'logisticsCompany' => ['logisticsCompany', 'SF Express'],
            'trackingNumber' => ['trackingNumber', 'SF1234567890'],
            'deliveredQuantity' => ['deliveredQuantity', '100'],
            'receivedBy' => ['receivedBy', 'John Doe'],
            'inspectedBy' => ['inspectedBy', 'Jane Smith'],
            'inspectionPassed' => ['inspectionPassed', true],
            'qualifiedQuantity' => ['qualifiedQuantity', '95'],
            'rejectedQuantity' => ['rejectedQuantity', '5'],
            'inspectionComment' => ['inspectionComment', '5 items damaged during shipping'],
            'warehousedBy' => ['warehousedBy', 'Bob Johnson'],
            'warehouseLocation' => ['warehouseLocation', 'A-12-3'],
            'discrepancyReason' => ['discrepancyReason', 'Quantity mismatch'],
            'attachments' => ['attachments', ['delivery_note' => 'delivery_note.pdf', 'inspection_report' => 'inspection_report.doc']],
        ];
    }
}
