<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\ProductCoreBundle\Entity\Sku;
use Tourze\ProductCoreBundle\Entity\Spu;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;
use Tourze\PurchaseManageBundle\Repository\PurchaseOrderItemRepository;

#[ORM\Table(name: 'purchase_order_item', options: ['comment' => '采购订单项'])]
#[ORM\Entity(repositoryClass: PurchaseOrderItemRepository::class)]
class PurchaseOrderItem
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '订单项ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: PurchaseOrder::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PurchaseOrder $purchaseOrder = null;

    #[ORM\ManyToOne(targetEntity: Spu::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Spu $spu = null;

    #[ORM\ManyToOne(targetEntity: Sku::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Sku $sku = null;

    #[Assert\NotBlank(message: '产品名称不能为空')]
    #[Assert\Length(max: 200, maxMessage: '产品名称不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 200, options: ['comment' => '产品名称'])]
    private string $productName = '';

    #[Assert\Length(max: 100, maxMessage: '产品编码不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '产品编码'])]
    private ?string $productCode = null;

    #[Assert\Length(max: 100, maxMessage: '产品规格不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '产品规格'])]
    private ?string $specification = null;

    #[Assert\Positive(message: '数量必须大于0')]
    #[Assert\Length(max: 20, maxMessage: '数量不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 4, options: ['default' => '1.0000', 'comment' => '采购数量'])]
    private string $quantity = '1.0000';

    #[Assert\Length(max: 10, maxMessage: '单位不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 10, options: ['default' => '个', 'comment' => '单位'])]
    private string $unit = '个';

    #[Assert\PositiveOrZero(message: '单价必须大于等于0')]
    #[Assert\Length(max: 20, maxMessage: '单价不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 4, options: ['default' => '0.0000', 'comment' => '单价'])]
    private string $unitPrice = '0.0000';

    #[Assert\PositiveOrZero(message: '小计必须大于等于0')]
    #[Assert\Length(max: 18, maxMessage: '小计不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00', 'comment' => '小计金额'])]
    private string $subtotal = '0.00';

    #[Assert\Range(min: 0, max: 100, notInRangeMessage: '税率必须在 {{ min }} 到 {{ max }} 之间')]
    #[Assert\Length(max: 8, maxMessage: '税率不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['default' => '0.00', 'comment' => '税率(%)'])]
    private string $taxRate = '0.00';

    #[Assert\PositiveOrZero(message: '税额必须大于等于0')]
    #[Assert\Length(max: 18, maxMessage: '税额不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00', 'comment' => '税额'])]
    private string $taxAmount = '0.00';

    #[Assert\Choice(callback: [DeliveryStatus::class, 'cases'], message: '请选择正确的状态')]
    #[ORM\Column(type: Types::STRING, length: 40, nullable: true, enumType: DeliveryStatus::class, options: ['default' => 'pending', 'comment' => '到货状态'])]
    private ?DeliveryStatus $deliveryStatus = DeliveryStatus::PENDING;

    #[Assert\PositiveOrZero(message: '已收数量必须大于等于0')]
    #[Assert\Length(max: 20, maxMessage: '已收数量不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 4, options: ['default' => '0.0000', 'comment' => '已收数量'])]
    private string $receivedQuantity = '0.0000';

    #[Assert\PositiveOrZero(message: '合格数量必须大于等于0')]
    #[Assert\Length(max: 20, maxMessage: '合格数量不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 4, options: ['default' => '0.0000', 'comment' => '合格数量'])]
    private string $qualifiedQuantity = '0.0000';

    #[Assert\Type(type: '\DateTimeImmutable', message: '期望交货日期格式不正确')]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '期望交货日期'])]
    private ?\DateTimeImmutable $expectedDeliveryDate = null;

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

    public function getSku(): ?Sku
    {
        return $this->sku;
    }

    public function setSku(?Sku $sku): void
    {
        $this->sku = $sku;
        if (null !== $sku) {
            $this->productCode = $sku->getGtin();
            $this->unit = $sku->getUnit() ?? '个';
            if (null !== $sku->getSpu()) {
                $this->productName = $sku->getSpu()->getTitle();
                $this->spu = $sku->getSpu();
            }
        }
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    public function getSpu(): ?Spu
    {
        return $this->spu;
    }

    public function setSpu(?Spu $spu): void
    {
        $this->spu = $spu;
        if (null !== $spu) {
            $this->productName = $spu->getTitle();
            $this->productCode = $spu->getGtin() ?? '';
        }
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): void
    {
        $this->productName = $productName;
    }

    public function getProductCode(): ?string
    {
        return $this->productCode;
    }

    public function setProductCode(?string $productCode): void
    {
        $this->productCode = $productCode;
    }

    public function getSpecification(): ?string
    {
        return $this->specification;
    }

    public function setSpecification(?string $specification): void
    {
        $this->specification = $specification;
    }

    public function getQuantity(): string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): void
    {
        $this->quantity = $quantity;
        $this->calculateSubtotal();
    }

    public function calculateSubtotal(): void
    {
        // 确保所有金额字段都是有效的数字字符串
        assert(is_numeric($this->quantity), 'Quantity must be numeric');
        assert(is_numeric($this->unitPrice), 'Unit price must be numeric');
        assert(is_numeric($this->taxRate), 'Tax rate must be numeric');

        $amount = bcmul($this->quantity, $this->unitPrice, 4);
        $this->subtotal = number_format((float) $amount, 2, '.', '');

        if (bccomp($this->taxRate, '0', 2) > 0) {
            $this->taxAmount = bcmul($this->subtotal, bcdiv($this->taxRate, '100', 4), 2);
        } else {
            $this->taxAmount = '0.00';
        }
    }

    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
        $this->calculateSubtotal();
    }

    public function getSubtotal(): string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): void
    {
        $this->subtotal = $subtotal;
    }

    public function getTaxRate(): string
    {
        return $this->taxRate;
    }

    public function setTaxRate(string $taxRate): void
    {
        $this->taxRate = $taxRate;
        $this->calculateSubtotal();
    }

    public function getTaxAmount(): string
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(string $taxAmount): void
    {
        $this->taxAmount = $taxAmount;
    }

    public function getDeliveryStatus(): ?DeliveryStatus
    {
        return $this->deliveryStatus;
    }

    public function setDeliveryStatus(?DeliveryStatus $deliveryStatus): void
    {
        $this->deliveryStatus = $deliveryStatus;
    }

    public function getReceivedQuantity(): string
    {
        return $this->receivedQuantity;
    }

    public function setReceivedQuantity(string $receivedQuantity): void
    {
        $this->receivedQuantity = $receivedQuantity;
    }

    public function getQualifiedQuantity(): string
    {
        return $this->qualifiedQuantity;
    }

    public function setQualifiedQuantity(string $qualifiedQuantity): void
    {
        $this->qualifiedQuantity = $qualifiedQuantity;
    }

    public function getExpectedDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->expectedDeliveryDate;
    }

    public function setExpectedDeliveryDate(?\DateTimeImmutable $expectedDeliveryDate): void
    {
        $this->expectedDeliveryDate = $expectedDeliveryDate;
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
        return $this->productName;
    }
}
