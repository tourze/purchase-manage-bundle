<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;
use Tourze\PurchaseManageBundle\Repository\PurchaseDeliveryRepository;

#[ORM\Table(name: 'purchase_delivery', options: ['comment' => '采购到货记录'])]
#[ORM\Entity(repositoryClass: PurchaseDeliveryRepository::class)]
class PurchaseDelivery
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '到货ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: PurchaseOrder::class, inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PurchaseOrder $purchaseOrder = null;

    #[Assert\NotBlank(message: '批次号不能为空')]
    #[Assert\Length(max: 50, maxMessage: '批次号不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '批次号'])]
    private string $batchNumber = '';

    #[Assert\Choice(callback: [DeliveryStatus::class, 'cases'], message: '请选择正确的状态')]
    #[ORM\Column(type: Types::STRING, length: 40, enumType: DeliveryStatus::class, options: ['default' => 'pending', 'comment' => '到货状态'])]
    private DeliveryStatus $status = DeliveryStatus::PENDING;

    #[Assert\Length(max: 100, maxMessage: '物流公司不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '物流公司'])]
    private ?string $logisticsCompany = null;

    #[Assert\Length(max: 100, maxMessage: '物流单号不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '物流单号'])]
    private ?string $trackingNumber = null;

    #[Assert\Type(type: '\DateTimeImmutable', message: '发货时间格式不正确')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '发货时间'])]
    private ?\DateTimeImmutable $shipTime = null;

    #[Assert\Type(type: '\DateTimeImmutable', message: '预计到达时间格式不正确')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '预计到达时间'])]
    private ?\DateTimeImmutable $estimatedArrivalTime = null;

    #[Assert\Type(type: '\DateTimeImmutable', message: '实际到达时间格式不正确')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '实际到达时间'])]
    private ?\DateTimeImmutable $actualArrivalTime = null;

    #[Assert\Type(type: '\DateTimeImmutable', message: '签收时间格式不正确')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '签收时间'])]
    private ?\DateTimeImmutable $receiveTime = null;

    #[Assert\Length(max: 100, maxMessage: '签收人不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '签收人'])]
    private ?string $receivedBy = null;

    #[Assert\Type(type: '\DateTimeImmutable', message: '检验时间格式不正确')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '检验时间'])]
    private ?\DateTimeImmutable $inspectTime = null;

    #[Assert\Length(max: 100, maxMessage: '检验人不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '检验人'])]
    private ?string $inspectedBy = null;

    #[Assert\Type(type: 'bool', message: '检验是否合格必须是布尔值')]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '检验是否合格'])]
    private bool $inspectionPassed = false;

    #[Assert\Length(max: 65535, maxMessage: '检验意见不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '检验意见'])]
    private ?string $inspectionComment = null;

    #[Assert\PositiveOrZero(message: '到货数量必须大于等于0')]
    #[Assert\Length(max: 20, maxMessage: '到货数量不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 4, options: ['default' => '0.0000', 'comment' => '到货数量'])]
    private string $deliveredQuantity = '0.0000';

    #[Assert\PositiveOrZero(message: '合格数量必须大于等于0')]
    #[Assert\Length(max: 20, maxMessage: '合格数量不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 4, options: ['default' => '0.0000', 'comment' => '合格数量'])]
    private string $qualifiedQuantity = '0.0000';

    #[Assert\PositiveOrZero(message: '不合格数量必须大于等于0')]
    #[Assert\Length(max: 20, maxMessage: '不合格数量不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 4, options: ['default' => '0.0000', 'comment' => '不合格数量'])]
    private string $rejectedQuantity = '0.0000';

    #[Assert\Length(max: 65535, maxMessage: '差异处理说明不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '差异处理说明'])]
    private ?string $discrepancyReason = null;

    #[Assert\Type(type: '\DateTimeImmutable', message: '入库时间格式不正确')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '入库时间'])]
    private ?\DateTimeImmutable $warehouseTime = null;

    #[Assert\Length(max: 100, maxMessage: '入库人不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '入库人'])]
    private ?string $warehousedBy = null;

    #[Assert\Length(max: 100, maxMessage: '库位不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '库位'])]
    private ?string $warehouseLocation = null;

    /**
     * @var array<string, string>|null
     */
    #[Assert\Type(type: 'array', message: '附件必须是数组格式')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '附件'])]
    private ?array $attachments = null;

    #[Assert\Length(max: 65535, maxMessage: '备注不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPurchaseOrder(): ?PurchaseOrder
    {
        return $this->purchaseOrder;
    }

    public function setPurchaseOrder(?PurchaseOrder $purchaseOrder): void
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    public function getBatchNumber(): string
    {
        return $this->batchNumber;
    }

    public function setBatchNumber(string $batchNumber): void
    {
        $this->batchNumber = $batchNumber;
    }

    public function getStatus(): DeliveryStatus
    {
        return $this->status;
    }

    public function setStatus(DeliveryStatus $status): void
    {
        $this->status = $status;
    }

    public function getLogisticsCompany(): ?string
    {
        return $this->logisticsCompany;
    }

    public function setLogisticsCompany(?string $logisticsCompany): void
    {
        $this->logisticsCompany = $logisticsCompany;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function getShipTime(): ?\DateTimeImmutable
    {
        return $this->shipTime;
    }

    public function setShipTime(?\DateTimeImmutable $shipTime): void
    {
        $this->shipTime = $shipTime;
    }

    public function getEstimatedArrivalTime(): ?\DateTimeImmutable
    {
        return $this->estimatedArrivalTime;
    }

    public function setEstimatedArrivalTime(?\DateTimeImmutable $estimatedArrivalTime): void
    {
        $this->estimatedArrivalTime = $estimatedArrivalTime;
    }

    public function getActualArrivalTime(): ?\DateTimeImmutable
    {
        return $this->actualArrivalTime;
    }

    public function setActualArrivalTime(?\DateTimeImmutable $actualArrivalTime): void
    {
        $this->actualArrivalTime = $actualArrivalTime;
    }

    public function getReceiveTime(): ?\DateTimeImmutable
    {
        return $this->receiveTime;
    }

    public function setReceiveTime(?\DateTimeImmutable $receiveTime): void
    {
        $this->receiveTime = $receiveTime;
    }

    public function getReceivedBy(): ?string
    {
        return $this->receivedBy;
    }

    public function setReceivedBy(?string $receivedBy): void
    {
        $this->receivedBy = $receivedBy;
    }

    public function getInspectTime(): ?\DateTimeImmutable
    {
        return $this->inspectTime;
    }

    public function setInspectTime(?\DateTimeImmutable $inspectTime): void
    {
        $this->inspectTime = $inspectTime;
    }

    public function getInspectedBy(): ?string
    {
        return $this->inspectedBy;
    }

    public function setInspectedBy(?string $inspectedBy): void
    {
        $this->inspectedBy = $inspectedBy;
    }

    public function isInspectionPassed(): bool
    {
        return $this->inspectionPassed;
    }

    public function setInspectionPassed(bool $inspectionPassed): void
    {
        $this->inspectionPassed = $inspectionPassed;
    }

    public function getInspectionComment(): ?string
    {
        return $this->inspectionComment;
    }

    public function setInspectionComment(?string $inspectionComment): void
    {
        $this->inspectionComment = $inspectionComment;
    }

    public function getDeliveredQuantity(): string
    {
        return $this->deliveredQuantity;
    }

    public function setDeliveredQuantity(string $deliveredQuantity): void
    {
        $this->deliveredQuantity = $deliveredQuantity;
    }

    public function getQualifiedQuantity(): string
    {
        return $this->qualifiedQuantity;
    }

    public function setQualifiedQuantity(string $qualifiedQuantity): void
    {
        $this->qualifiedQuantity = $qualifiedQuantity;
    }

    public function getRejectedQuantity(): string
    {
        return $this->rejectedQuantity;
    }

    public function setRejectedQuantity(string $rejectedQuantity): void
    {
        $this->rejectedQuantity = $rejectedQuantity;
    }

    public function getDiscrepancyReason(): ?string
    {
        return $this->discrepancyReason;
    }

    public function setDiscrepancyReason(?string $discrepancyReason): void
    {
        $this->discrepancyReason = $discrepancyReason;
    }

    public function getWarehouseTime(): ?\DateTimeImmutable
    {
        return $this->warehouseTime;
    }

    public function setWarehouseTime(?\DateTimeImmutable $warehouseTime): void
    {
        $this->warehouseTime = $warehouseTime;
    }

    public function getWarehousedBy(): ?string
    {
        return $this->warehousedBy;
    }

    public function setWarehousedBy(?string $warehousedBy): void
    {
        $this->warehousedBy = $warehousedBy;
    }

    public function getWarehouseLocation(): ?string
    {
        return $this->warehouseLocation;
    }

    public function setWarehouseLocation(?string $warehouseLocation): void
    {
        $this->warehouseLocation = $warehouseLocation;
    }

    /**
     * @return array<string, string>|null
     */
    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    /**
     * @param array<string, string>|null $attachments
     */
    public function setAttachments(?array $attachments): void
    {
        $this->attachments = $attachments;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->batchNumber, $this->status->getLabel());
    }
}
