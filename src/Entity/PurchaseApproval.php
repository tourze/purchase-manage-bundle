<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\PurchaseManageBundle\Enum\ApprovalStatus;
use Tourze\PurchaseManageBundle\Repository\PurchaseApprovalRepository;

#[ORM\Table(name: 'purchase_approval', options: ['comment' => '采购审批记录'])]
#[ORM\Entity(repositoryClass: PurchaseApprovalRepository::class)]
class PurchaseApproval
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '审批ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: PurchaseOrder::class, inversedBy: 'approvals')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PurchaseOrder $purchaseOrder = null;

    #[Assert\NotBlank(message: '审批级别不能为空')]
    #[Assert\Length(max: 50, maxMessage: '审批级别不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '审批级别'])]
    private string $level = '';

    #[Assert\Positive(message: '审批顺序必须大于0')]
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1, 'comment' => '审批顺序'])]
    private int $sequence = 1;

    #[Assert\Choice(callback: [ApprovalStatus::class, 'cases'], message: '请选择正确的状态')]
    #[ORM\Column(type: Types::STRING, length: 40, enumType: ApprovalStatus::class, options: ['default' => 'pending', 'comment' => '审批状态'])]
    private ApprovalStatus $status = ApprovalStatus::PENDING;

    #[Assert\Positive(message: '审批人ID必须大于0')]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '审批人ID'])]
    private ?int $approverId = null;

    #[Assert\Length(max: 100, maxMessage: '审批人姓名不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '审批人姓名'])]
    private ?string $approverName = null;

    #[Assert\Length(max: 50, maxMessage: '审批角色不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '审批角色'])]
    private ?string $approverRole = null;

    #[Assert\Length(max: 65535, maxMessage: '审批意见不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '审批意见'])]
    private ?string $comment = null;

    #[Assert\Type(type: '\DateTimeImmutable', message: '审批时间格式不正确')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '审批时间'])]
    private ?\DateTimeImmutable $approveTime = null;

    #[Assert\Length(max: 20, maxMessage: '审批金额限制不能超过 {{ limit }} 个字符')]
    #[Assert\Regex(pattern: '/^\d{1,13}(\.\d{1,2})?$/', message: '审批金额限制格式不正确')]
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true, options: ['comment' => '审批金额限制'])]
    private ?string $amountLimit = null;

    /**
     * @var array<string, string>|null
     */
    #[Assert\Type(type: 'array', message: '附件必须是数组格式')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '附件'])]
    private ?array $attachments = null;

    #[Assert\Type(type: 'bool', message: '是否需要会签必须是布尔值')]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否需要会签'])]
    private bool $requireCountersign = false;

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

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getStatus(): ApprovalStatus
    {
        return $this->status;
    }

    public function setStatus(ApprovalStatus $status): void
    {
        $this->status = $status;
    }

    public function getApproverId(): ?int
    {
        return $this->approverId;
    }

    public function setApproverId(?int $approverId): void
    {
        $this->approverId = $approverId;
    }

    public function getApproverName(): ?string
    {
        return $this->approverName;
    }

    public function setApproverName(?string $approverName): void
    {
        $this->approverName = $approverName;
    }

    public function getApproverRole(): ?string
    {
        return $this->approverRole;
    }

    public function setApproverRole(?string $approverRole): void
    {
        $this->approverRole = $approverRole;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getApproveTime(): ?\DateTimeImmutable
    {
        return $this->approveTime;
    }

    public function setApproveTime(?\DateTimeImmutable $approveTime): void
    {
        $this->approveTime = $approveTime;
    }

    public function getAmountLimit(): ?string
    {
        return $this->amountLimit;
    }

    public function setAmountLimit(?string $amountLimit): void
    {
        $this->amountLimit = $amountLimit;
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

    public function isRequireCountersign(): bool
    {
        return $this->requireCountersign;
    }

    public function setRequireCountersign(bool $requireCountersign): void
    {
        $this->requireCountersign = $requireCountersign;
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
        return sprintf('%s - %s', $this->level, $this->status->getLabel());
    }
}
