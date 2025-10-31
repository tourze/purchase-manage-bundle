<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;
use Tourze\PurchaseManageBundle\Repository\PurchaseOrderRepository;
use Tourze\SupplierManageBundle\Entity\Supplier;

#[ORM\Table(name: 'purchase_order', options: ['comment' => '采购订单'])]
#[ORM\Entity(repositoryClass: PurchaseOrderRepository::class)]
class PurchaseOrder
{
    use TimestampableAware;
    use BlameableAware;
    use CreatedFromIpAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '订单ID'])]
    private int $id = 0;

    #[SnowflakeColumn(prefix: 'PO')]
    #[Assert\Length(max: 40, maxMessage: '订单编号不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 40, unique: true, nullable: true, options: ['comment' => '订单编号'])]
    private ?string $orderNumber = null;

    #[Assert\NotBlank(message: '订单标题不能为空')]
    #[Assert\Length(max: 200, maxMessage: '订单标题不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 200, options: ['comment' => '订单标题'])]
    private string $title = '';

    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(name: 'supplier_id', nullable: false)]
    #[Assert\NotNull(message: '供应商不能为空')]
    private ?Supplier $supplier = null;

    #[Assert\Choice(callback: [PurchaseOrderStatus::class, 'cases'], message: '请选择正确的状态')]
    #[ORM\Column(type: Types::STRING, length: 40, enumType: PurchaseOrderStatus::class, options: ['default' => 'draft', 'comment' => '订单状态'])]
    private PurchaseOrderStatus $status = PurchaseOrderStatus::DRAFT;

    #[Assert\PositiveOrZero(message: '订单总金额必须大于等于0')]
    #[Assert\Length(max: 18, maxMessage: '订单总金额不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00', 'comment' => '订单总金额'])]
    private string $totalAmount = '0.00';

    #[Assert\PositiveOrZero(message: '税额必须大于等于0')]
    #[Assert\Length(max: 18, maxMessage: '税额不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00', 'comment' => '税额'])]
    private string $taxAmount = '0.00';

    #[Assert\PositiveOrZero(message: '运费必须大于等于0')]
    #[Assert\Length(max: 18, maxMessage: '运费不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00', 'comment' => '运费'])]
    private string $shippingAmount = '0.00';

    #[Assert\PositiveOrZero(message: '折扣金额必须大于等于0')]
    #[Assert\Length(max: 18, maxMessage: '折扣金额不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00', 'comment' => '折扣金额'])]
    private string $discountAmount = '0.00';

    #[Assert\PositiveOrZero(message: '应付金额必须大于等于0')]
    #[Assert\Length(max: 18, maxMessage: '应付金额不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00', 'comment' => '应付金额'])]
    private string $payableAmount = '0.00';

    #[Assert\Length(max: 3, maxMessage: '货币代码不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 3, options: ['default' => 'CNY', 'comment' => '货币代码'])]
    private string $currency = 'CNY';

    #[Assert\Type(type: '\DateTimeImmutable', message: '期望交货日期格式不正确')]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '期望交货日期'])]
    private ?\DateTimeImmutable $expectedDeliveryDate = null;

    #[Assert\Type(type: '\DateTimeImmutable', message: '实际交货日期格式不正确')]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '实际交货日期'])]
    private ?\DateTimeImmutable $actualDeliveryDate = null;

    #[Assert\Length(max: 500, maxMessage: '收货地址不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '收货地址'])]
    private ?string $deliveryAddress = null;

    #[Assert\Length(max: 100, maxMessage: '付款条款不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '付款条款'])]
    private ?string $paymentTerms = null;

    #[Assert\Length(max: 65535, maxMessage: '订单备注不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '订单备注'])]
    private ?string $remark = null;

    #[Assert\Type(type: '\DateTimeImmutable', message: '审批时间格式不正确')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '审批时间'])]
    private ?\DateTimeImmutable $approveTime = null;

    #[Assert\Positive(message: '审批人ID必须大于0')]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '审批人ID'])]
    private ?int $approvedBy = null;

    #[Assert\Length(max: 65535, maxMessage: '审批意见不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '审批意见'])]
    private ?string $approvalComment = null;

    #[Assert\Type(type: '\DateTimeImmutable', message: '取消时间格式不正确')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '取消时间'])]
    private ?\DateTimeImmutable $cancelTime = null;

    #[Assert\Length(max: 65535, maxMessage: '取消原因不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '取消原因'])]
    private ?string $cancelReason = null;

    /**
     * @var Collection<int, PurchaseOrderItem>
     */
    #[ORM\OneToMany(mappedBy: 'purchaseOrder', targetEntity: PurchaseOrderItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $items;

    /**
     * @var Collection<int, PurchaseApproval>
     */
    #[ORM\OneToMany(mappedBy: 'purchaseOrder', targetEntity: PurchaseApproval::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $approvals;

    /**
     * @var Collection<int, PurchaseDelivery>
     */
    #[ORM\OneToMany(mappedBy: 'purchaseOrder', targetEntity: PurchaseDelivery::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $deliveries;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->approvals = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function getStatus(): PurchaseOrderStatus
    {
        return $this->status;
    }

    public function setStatus(PurchaseOrderStatus $status): void
    {
        $this->status = $status;
    }

    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getTaxAmount(): string
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(string $taxAmount): void
    {
        $this->taxAmount = $taxAmount;
    }

    public function getShippingAmount(): string
    {
        return $this->shippingAmount;
    }

    public function setShippingAmount(string $shippingAmount): void
    {
        $this->shippingAmount = $shippingAmount;
    }

    public function getDiscountAmount(): string
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(string $discountAmount): void
    {
        $this->discountAmount = $discountAmount;
    }

    public function getPayableAmount(): string
    {
        return $this->payableAmount;
    }

    public function setPayableAmount(string $payableAmount): void
    {
        $this->payableAmount = $payableAmount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getExpectedDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->expectedDeliveryDate;
    }

    public function setExpectedDeliveryDate(?\DateTimeImmutable $expectedDeliveryDate): void
    {
        $this->expectedDeliveryDate = $expectedDeliveryDate;
    }

    public function getActualDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->actualDeliveryDate;
    }

    public function setActualDeliveryDate(?\DateTimeImmutable $actualDeliveryDate): void
    {
        $this->actualDeliveryDate = $actualDeliveryDate;
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?string $deliveryAddress): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    public function getPaymentTerms(): ?string
    {
        return $this->paymentTerms;
    }

    public function setPaymentTerms(?string $paymentTerms): void
    {
        $this->paymentTerms = $paymentTerms;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getApproveTime(): ?\DateTimeImmutable
    {
        return $this->approveTime;
    }

    public function setApproveTime(?\DateTimeImmutable $approveTime): void
    {
        $this->approveTime = $approveTime;
    }

    public function getApprovedBy(): ?int
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?int $approvedBy): void
    {
        $this->approvedBy = $approvedBy;
    }

    public function getApprovalComment(): ?string
    {
        return $this->approvalComment;
    }

    public function setApprovalComment(?string $approvalComment): void
    {
        $this->approvalComment = $approvalComment;
    }

    public function getCancelTime(): ?\DateTimeImmutable
    {
        return $this->cancelTime;
    }

    public function setCancelTime(?\DateTimeImmutable $cancelTime): void
    {
        $this->cancelTime = $cancelTime;
    }

    public function getCancelReason(): ?string
    {
        return $this->cancelReason;
    }

    public function setCancelReason(?string $cancelReason): void
    {
        $this->cancelReason = $cancelReason;
    }

    /**
     * @return Collection<int, PurchaseOrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(PurchaseOrderItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setPurchaseOrder($this);
        }
    }

    public function removeItem(PurchaseOrderItem $item): void
    {
        if ($this->items->removeElement($item)) {
            if ($item->getPurchaseOrder() === $this) {
                $item->setPurchaseOrder(null);
            }
        }
    }

    /**
     * @return Collection<int, PurchaseApproval>
     */
    public function getApprovals(): Collection
    {
        return $this->approvals;
    }

    /**
     * @return Collection<int, PurchaseDelivery>
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function calculateTotalAmount(): void
    {
        $subtotal = '0.00';
        foreach ($this->items as $item) {
            $itemSubtotal = $item->getSubtotal();
            assert(is_numeric($itemSubtotal), 'Item subtotal must be numeric');
            $subtotal = bcadd($subtotal, $itemSubtotal, 2);
        }

        $this->totalAmount = $subtotal;

        // 确保所有金额字段都是有效的数字字符串
        assert(is_numeric($this->taxAmount), 'Tax amount must be numeric');
        assert(is_numeric($this->discountAmount), 'Discount amount must be numeric');
        assert(is_numeric($this->shippingAmount), 'Shipping amount must be numeric');

        $this->payableAmount = bcadd(
            bcsub(
                bcadd($subtotal, $this->taxAmount, 2),
                $this->discountAmount,
                2
            ),
            $this->shippingAmount,
            2
        );
    }

    public function __toString(): string
    {
        return $this->orderNumber ?? $this->title;
    }
}
