<?php

namespace Tourze\PurchaseManageBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Tourze\ProductCoreBundle\Entity\Sku;
use Tourze\ProductCoreBundle\Entity\Spu;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrderItem;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;
use Tourze\PurchaseManageBundle\Event\PurchaseOrderCreatedEvent;
use Tourze\PurchaseManageBundle\Event\PurchaseOrderStatusChangedEvent;
use Tourze\PurchaseManageBundle\Repository\PurchaseOrderRepository;
use Tourze\SupplierManageBundle\Entity\Supplier;
use Tourze\SupplierManageBundle\Service\SupplierService;

#[Autoconfigure(public: true)]
class PurchaseOrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PurchaseOrderRepository $purchaseOrderRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ?WorkflowInterface $purchaseOrderStateMachine,
        private readonly ?SupplierService $supplierService,
    ) {
    }

    /**
     * 创建采购订单
     *
     * @param array<array<string, mixed>> $items
     */
    public function createOrder(Supplier $supplier, array $items = []): PurchaseOrder
    {
        $order = new PurchaseOrder();
        $order->setSupplier($supplier);
        $order->setStatus(PurchaseOrderStatus::DRAFT);
        $order->setTitle(sprintf('采购订单 - %s', $supplier->getName()));

        foreach ($items as $itemData) {
            $item = $this->createOrderItem($itemData);
            $order->addItem($item);
        }

        $order->calculateTotalAmount();

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            new PurchaseOrderCreatedEvent($order)
        );

        return $order;
    }

    /**
     * 创建订单项
     *
     * @param array<string, mixed> $data
     */
    private function createOrderItem(array $data): PurchaseOrderItem
    {
        $item = new PurchaseOrderItem();

        $this->setOrderItemProducts($item, $data);
        $this->setOrderItemBasicProperties($item, $data);
        $this->setOrderItemOptionalProperties($item, $data);

        $item->calculateSubtotal();

        return $item;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setOrderItemProducts(PurchaseOrderItem $item, array $data): void
    {
        if (isset($data['sku']) && $data['sku'] instanceof Sku) {
            $item->setSku($data['sku']);
        } elseif (isset($data['spu']) && $data['spu'] instanceof Spu) {
            $item->setSpu($data['spu']);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setOrderItemBasicProperties(PurchaseOrderItem $item, array $data): void
    {
        $productName = $data['productName'] ?? '';
        $item->setProductName(is_string($productName) ? $productName : '');

        $quantity = $data['quantity'] ?? '1';
        $item->setQuantity(is_string($quantity) || is_numeric($quantity) ? (string) $quantity : '1');

        $unitPrice = $data['unitPrice'] ?? '0';
        $item->setUnitPrice(is_string($unitPrice) || is_numeric($unitPrice) ? (string) $unitPrice : '0');

        $unit = $data['unit'] ?? '个';
        $item->setUnit(is_string($unit) ? $unit : '个');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setOrderItemOptionalProperties(PurchaseOrderItem $item, array $data): void
    {
        $taxRate = $data['taxRate'] ?? '0';
        $item->setTaxRate(is_string($taxRate) || is_numeric($taxRate) ? (string) $taxRate : '0');

        $specification = $data['specification'] ?? null;
        $item->setSpecification(is_string($specification) ? $specification : null);

        $expectedDeliveryDate = $data['expectedDeliveryDate'] ?? null;
        if ($expectedDeliveryDate instanceof \DateTimeImmutable) {
            $item->setExpectedDeliveryDate($expectedDeliveryDate);
        }
    }

    /**
     * 提交订单审批
     */
    public function submitForApproval(PurchaseOrder $order): bool
    {
        return $this->updateOrderStatus($order, 'submit_for_approval');
    }

    /**
     * 更新订单状态
     */
    public function updateOrderStatus(PurchaseOrder $order, string $transition): bool
    {
        if (null !== $this->purchaseOrderStateMachine) {
            if (!$this->purchaseOrderStateMachine->can($order, $transition)) {
                return false;
            }
        } else {
            // 当工作流不可用时，手动验证状态转换
            if (!$this->isValidTransition($order, $transition)) {
                return false;
            }
        }

        $oldStatus = $order->getStatus();
        if (null !== $this->purchaseOrderStateMachine) {
            $this->purchaseOrderStateMachine->apply($order, $transition);
        } else {
            // 手动处理状态转换
            $newStatus = $this->getNewStatusFromTransition($transition);
            if (null === $newStatus) {
                return false;
            }
            $order->setStatus($newStatus);
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            new PurchaseOrderStatusChangedEvent($order, $oldStatus, $order->getStatus())
        );

        return true;
    }

    /**
     * 验证状态转换是否有效
     */
    private function isValidTransition(PurchaseOrder $order, string $transition): bool
    {
        $currentStatus = $order->getStatus();

        // 定义有效的状态转换规则
        $validTransitions = [
            PurchaseOrderStatus::DRAFT->value => ['submit_for_approval', 'cancel'],
            PurchaseOrderStatus::PENDING_APPROVAL->value => ['approve', 'reject', 'cancel'],
            PurchaseOrderStatus::APPROVED->value => ['cancel'],
            PurchaseOrderStatus::REJECTED->value => ['submit_for_approval', 'cancel'],
            PurchaseOrderStatus::CANCELLED->value => [],
        ];

        return isset($validTransitions[$currentStatus->value])
               && in_array($transition, $validTransitions[$currentStatus->value], true);
    }

    /**
     * 根据转换获取新状态
     */
    private function getNewStatusFromTransition(string $transition): ?PurchaseOrderStatus
    {
        return match ($transition) {
            'submit_for_approval' => PurchaseOrderStatus::PENDING_APPROVAL,
            'approve' => PurchaseOrderStatus::APPROVED,
            'reject' => PurchaseOrderStatus::REJECTED,
            'cancel' => PurchaseOrderStatus::CANCELLED,
            default => null,
        };
    }

    /**
     * 批准订单
     */
    public function approveOrder(PurchaseOrder $order, ?int $approverId = null, ?string $comment = null): bool
    {
        if (!$this->updateOrderStatus($order, 'approve')) {
            return false;
        }

        $order->setApproveTime(new \DateTimeImmutable());
        $order->setApprovedBy($approverId);
        $order->setApprovalComment($comment);

        $this->entityManager->flush();

        return true;
    }

    /**
     * 拒绝订单
     */
    public function rejectOrder(PurchaseOrder $order, ?string $reason = null): bool
    {
        if (!$this->updateOrderStatus($order, 'reject')) {
            return false;
        }

        $order->setApprovalComment($reason);

        $this->entityManager->flush();

        return true;
    }

    /**
     * 取消订单
     */
    public function cancelOrder(PurchaseOrder $order, ?string $reason = null): bool
    {
        if (!$this->updateOrderStatus($order, 'cancel')) {
            return false;
        }

        $order->setCancelTime(new \DateTimeImmutable());
        $order->setCancelReason($reason);

        $this->entityManager->flush();

        return true;
    }

    /**
     * 查找待审批订单
     *
     * @return PurchaseOrder[]
     */
    public function findPendingApprovalOrders(): array
    {
        return $this->purchaseOrderRepository->findPendingApproval();
    }

    /**
     * 查找指定供应商的订单
     *
     * @return PurchaseOrder[]
     */
    public function findOrdersBySupplier(Supplier $supplier): array
    {
        $supplierId = $supplier->getId();
        if (null === $supplierId) {
            return [];
        }

        return $this->purchaseOrderRepository->findBySupplier($supplierId);
    }

    /**
     * 获取活跃供应商列表
     *
     * @return Supplier[]
     */
    public function getActiveSuppliers(): array
    {
        if (null !== $this->supplierService) {
            return $this->supplierService->getActiveSuppliers();
        }

        return [];
    }

    /**
     * 搜索订单
     *
     * @param array<string, mixed> $criteria
     * @return PurchaseOrder[]
     */
    public function searchOrders(array $criteria): array
    {
        return $this->purchaseOrderRepository->searchOrders($criteria);
    }

    /**
     * 获取订单统计
     *
     * @return array<string, mixed>
     */
    public function getOrderStatistics(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        return $this->purchaseOrderRepository->getOrderStatistics($startDate, $endDate);
    }
}
