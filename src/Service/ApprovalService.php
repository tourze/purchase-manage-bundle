<?php

namespace Tourze\PurchaseManageBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Workflow\WorkflowInterface;
use Tourze\PurchaseManageBundle\Entity\PurchaseApproval;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\ApprovalStatus;
use Tourze\PurchaseManageBundle\Repository\PurchaseApprovalRepository;

#[Autoconfigure(public: true)]
class ApprovalService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PurchaseApprovalRepository $approvalRepository,
        private readonly ?WorkflowInterface $purchaseApprovalWorkflow,
        private readonly PurchaseOrderService $purchaseOrderService,
    ) {
    }

    /**
     * 创建审批流程
     * @param array<array<string, mixed>> $levels
     * @return PurchaseApproval[]
     */
    public function createApprovalFlow(PurchaseOrder $order, array $levels): array
    {
        $approvals = [];
        $sequence = 1;

        foreach ($levels as $level) {
            $approval = new PurchaseApproval();
            $approval->setPurchaseOrder($order);
            $levelName = $level['level'] ?? '一级审批';
            $approval->setLevel(is_string($levelName) ? $levelName : '一级审批');
            $approval->setSequence($sequence++);
            $approval->setStatus(ApprovalStatus::PENDING);

            $role = $level['role'] ?? null;
            $approval->setApproverRole(is_string($role) ? $role : null);

            $amountLimit = $level['amountLimit'] ?? null;
            $approval->setAmountLimit(is_string($amountLimit) || is_numeric($amountLimit) ? (string) $amountLimit : null);

            $requireCountersign = $level['requireCountersign'] ?? false;
            $approval->setRequireCountersign(is_bool($requireCountersign) ? $requireCountersign : false);

            $this->entityManager->persist($approval);
            $approvals[] = $approval;
        }

        $this->entityManager->flush();

        return $approvals;
    }

    /**
     * 处理审批
     */
    public function processApproval(
        PurchaseApproval $approval,
        int $approverId,
        bool $approved,
        ?string $comment = null,
    ): bool {
        if (ApprovalStatus::PENDING !== $approval->getStatus()) {
            return false;
        }

        $transition = $approved ? 'approve' : 'reject';

        if (!$this->applyWorkflowTransition($approval, $transition)) {
            return false;
        }

        $approval->setApproverId($approverId);
        $approval->setApproveTime(new \DateTimeImmutable());
        $approval->setComment($comment);

        $this->entityManager->flush();

        if ($approved) {
            $this->handleApprovalApproved($approval);
        } else {
            $this->handleApprovalRejected($approval);
        }

        return true;
    }

    private function applyWorkflowTransition(PurchaseApproval $approval, string $transition): bool
    {
        if (null === $this->purchaseApprovalWorkflow) {
            return true;
        }

        if (!$this->purchaseApprovalWorkflow->can($approval, $transition)) {
            return false;
        }

        $this->purchaseApprovalWorkflow->apply($approval, $transition);

        return true;
    }

    /**
     * 处理审批通过
     */
    private function handleApprovalApproved(PurchaseApproval $approval): void
    {
        $order = $approval->getPurchaseOrder();
        if (null === $order) {
            return;
        }

        $pendingApprovals = $this->approvalRepository->findPendingApprovals();
        $orderPendingApprovals = array_filter(
            $pendingApprovals,
            fn ($a) => $a->getPurchaseOrder()?->getId() === $order->getId()
        );

        if ([] === $orderPendingApprovals) {
            $approverId = $approval->getApproverId();
            if (null !== $approverId) {
                $this->purchaseOrderService->approveOrder(
                    $order,
                    $approverId,
                    '所有审批已通过'
                );
            }
        }
    }

    /**
     * 处理审批拒绝
     */
    private function handleApprovalRejected(PurchaseApproval $approval): void
    {
        $order = $approval->getPurchaseOrder();
        if (null === $order) {
            return;
        }

        $reason = sprintf(
            '%s 拒绝：%s',
            $approval->getLevel(),
            $approval->getComment() ?? '无'
        );

        $this->purchaseOrderService->rejectOrder($order, $reason);

        $this->cancelPendingApprovals($order);
    }

    /**
     * 取消待审批记录
     */
    private function cancelPendingApprovals(PurchaseOrder $order): void
    {
        $approvals = $this->approvalRepository->findByOrder($order->getId());

        foreach ($approvals as $approval) {
            if (ApprovalStatus::PENDING !== $approval->getStatus()) {
                continue;
            }

            $this->cancelSingleApproval($approval);
        }

        $this->entityManager->flush();
    }

    private function cancelSingleApproval(PurchaseApproval $approval): void
    {
        if ($this->canUseWorkflow($approval, 'cancel')) {
            $this->purchaseApprovalWorkflow?->apply($approval, 'cancel');

            return;
        }

        // 在测试环境中直接设置状态
        $approval->setStatus(ApprovalStatus::CANCELLED);
    }

    private function canUseWorkflow(PurchaseApproval $approval, string $transition): bool
    {
        return null !== $this->purchaseApprovalWorkflow
            && $this->purchaseApprovalWorkflow->can($approval, $transition);
    }

    /**
     * 获取待审批记录
     * @return PurchaseApproval[]
     */
    public function getPendingApprovals(?int $approverId = null): array
    {
        return $this->approvalRepository->findPendingApprovals($approverId);
    }

    /**
     * 获取审批历史
     * @return PurchaseApproval[]
     */
    public function getApprovalHistory(PurchaseOrder $order): array
    {
        return $this->approvalRepository->findByOrder($order->getId());
    }

    /**
     * 根据金额获取审批级别
     * @return array<array<string, mixed>>
     */
    public function getApprovalLevelsByAmount(string $amount): array
    {
        $levels = [];
        $amountFloat = (float) $amount;

        if ($amountFloat < 10000) {
            $levels[] = [
                'level' => '部门经理审批',
                'role' => 'ROLE_MANAGER',
                'amountLimit' => '10000',
            ];
        } elseif ($amountFloat < 50000) {
            $levels[] = [
                'level' => '部门经理审批',
                'role' => 'ROLE_MANAGER',
                'amountLimit' => '50000',
            ];
            $levels[] = [
                'level' => '财务审批',
                'role' => 'ROLE_FINANCE',
                'amountLimit' => '50000',
            ];
        } else {
            $levels[] = [
                'level' => '部门经理审批',
                'role' => 'ROLE_MANAGER',
            ];
            $levels[] = [
                'level' => '财务审批',
                'role' => 'ROLE_FINANCE',
            ];
            $levels[] = [
                'level' => '总经理审批',
                'role' => 'ROLE_DIRECTOR',
                'amountLimit' => null,
            ];
        }

        return $levels;
    }
}
