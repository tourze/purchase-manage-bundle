<?php

namespace Tourze\PurchaseManageBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Workflow\WorkflowInterface;
use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;
use Tourze\PurchaseManageBundle\Repository\PurchaseDeliveryRepository;

#[Autoconfigure(public: true)]
class DeliveryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PurchaseDeliveryRepository $deliveryRepository,
        private readonly ?WorkflowInterface $purchaseDeliveryStateMachine,
        private readonly PurchaseOrderService $purchaseOrderService,
    ) {
    }

    /**
     * 创建到货记录
     */
    public function createDelivery(PurchaseOrder $order, string $batchNumber): PurchaseDelivery
    {
        $delivery = new PurchaseDelivery();
        $delivery->setPurchaseOrder($order);
        $delivery->setBatchNumber($batchNumber);
        $delivery->setStatus(DeliveryStatus::PENDING);

        $this->entityManager->persist($delivery);
        $this->entityManager->flush();

        return $delivery;
    }

    /**
     * 标记发货
     */
    public function markAsShipped(
        PurchaseDelivery $delivery,
        ?string $logisticsCompany = null,
        ?string $trackingNumber = null,
        ?\DateTimeImmutable $estimatedArrivalAt = null,
    ): bool {
        if (null !== $this->purchaseDeliveryStateMachine) {
            if (!$this->purchaseDeliveryStateMachine->can($delivery, 'ship')) {
                return false;
            }

            $this->purchaseDeliveryStateMachine->apply($delivery, 'ship');
        }

        $delivery->setShipTime(new \DateTimeImmutable());
        $delivery->setLogisticsCompany($logisticsCompany);
        $delivery->setTrackingNumber($trackingNumber);
        $delivery->setEstimatedArrivalTime($estimatedArrivalAt);

        $this->entityManager->flush();

        $order = $delivery->getPurchaseOrder();
        if (null !== $order) {
            $this->purchaseOrderService->updateOrderStatus($order, 'mark_shipped');
        }

        return true;
    }

    /**
     * 标记运输中
     */
    public function markInTransit(PurchaseDelivery $delivery): bool
    {
        if (null !== $this->purchaseDeliveryStateMachine) {
            if (!$this->purchaseDeliveryStateMachine->can($delivery, 'in_transit')) {
                return false;
            }

            $this->purchaseDeliveryStateMachine->apply($delivery, 'in_transit');
        }
        $this->entityManager->flush();

        return true;
    }

    /**
     * 标记到达
     */
    public function markAsArrived(PurchaseDelivery $delivery): bool
    {
        if (null !== $this->purchaseDeliveryStateMachine) {
            if (!$this->purchaseDeliveryStateMachine->can($delivery, 'arrive')) {
                return false;
            }

            $this->purchaseDeliveryStateMachine->apply($delivery, 'arrive');
        }
        $delivery->setActualArrivalTime(new \DateTimeImmutable());

        $this->entityManager->flush();

        return true;
    }

    /**
     * 签收货物
     */
    public function receiveDelivery(
        PurchaseDelivery $delivery,
        string $receivedBy,
        string $deliveredQuantity,
    ): bool {
        if (null !== $this->purchaseDeliveryStateMachine) {
            if (!$this->purchaseDeliveryStateMachine->can($delivery, 'receive')) {
                return false;
            }

            $this->purchaseDeliveryStateMachine->apply($delivery, 'receive');
        }

        $delivery->setReceiveTime(new \DateTimeImmutable());
        $delivery->setReceivedBy($receivedBy);
        $delivery->setDeliveredQuantity($deliveredQuantity);

        $this->entityManager->flush();

        $order = $delivery->getPurchaseOrder();
        if (null !== $order) {
            $this->purchaseOrderService->updateOrderStatus($order, 'mark_received');
        }

        return true;
    }

    /**
     * 检验货物
     */
    public function inspectDelivery(
        PurchaseDelivery $delivery,
        string $inspectedBy,
        bool $passed,
        string $qualifiedQuantity,
        string $rejectedQuantity = '0',
        ?string $comment = null,
    ): bool {
        if (null !== $this->purchaseDeliveryStateMachine) {
            if (!$this->purchaseDeliveryStateMachine->can($delivery, 'inspect')) {
                return false;
            }

            $this->purchaseDeliveryStateMachine->apply($delivery, 'inspect');
        }

        $delivery->setInspectTime(new \DateTimeImmutable());
        $delivery->setInspectedBy($inspectedBy);
        $delivery->setInspectionPassed($passed);
        $delivery->setQualifiedQuantity($qualifiedQuantity);
        $delivery->setRejectedQuantity($rejectedQuantity);
        $delivery->setInspectionComment($comment);

        if ($rejectedQuantity > 0) {
            $delivery->setDiscrepancyReason('质量检验不合格数量：' . $rejectedQuantity);
        }

        $this->entityManager->flush();

        $this->updateOrderItemsDeliveryStatus($delivery);

        return true;
    }

    /**
     * 更新订单项的到货状态
     */
    private function updateOrderItemsDeliveryStatus(PurchaseDelivery $delivery): void
    {
        $order = $delivery->getPurchaseOrder();
        if (null === $order) {
            return;
        }

        foreach ($order->getItems() as $item) {
            $item->setDeliveryStatus(DeliveryStatus::RECEIVED);
            $item->setReceivedQuantity($delivery->getDeliveredQuantity());
            $item->setQualifiedQuantity($delivery->getQualifiedQuantity());
        }

        $this->entityManager->flush();
    }

    /**
     * 入库
     */
    public function warehouseDelivery(
        PurchaseDelivery $delivery,
        string $warehousedBy,
        ?string $warehouseLocation = null,
    ): bool {
        if (null !== $this->purchaseDeliveryStateMachine) {
            if (!$this->purchaseDeliveryStateMachine->can($delivery, 'warehouse')) {
                return false;
            }

            $this->purchaseDeliveryStateMachine->apply($delivery, 'warehouse');
        }

        $delivery->setWarehouseTime(new \DateTimeImmutable());
        $delivery->setWarehousedBy($warehousedBy);
        $delivery->setWarehouseLocation($warehouseLocation);

        $this->entityManager->flush();

        $order = $delivery->getPurchaseOrder();
        if (null !== $order) {
            $allDelivered = $this->checkAllItemsDelivered($order);

            if ($allDelivered) {
                $this->purchaseOrderService->updateOrderStatus($order, 'complete');
            }
        }

        return true;
    }

    /**
     * 检查所有订单项是否已到货
     */
    private function checkAllItemsDelivered(PurchaseOrder $order): bool
    {
        $deliveries = $this->deliveryRepository->findByOrder($order->getId());

        if ([] === $deliveries) {
            return false;
        }

        foreach ($deliveries as $delivery) {
            if (DeliveryStatus::WAREHOUSED !== $delivery->getStatus()) {
                return false;
            }
        }

        return true;
    }

    /**
     * 获取在途物流
     * @return PurchaseDelivery[]
     */
    public function getInTransitDeliveries(): array
    {
        return $this->deliveryRepository->findInTransit();
    }

    /**
     * 获取待检验到货
     * @return PurchaseDelivery[]
     */
    public function getPendingInspectionDeliveries(): array
    {
        return $this->deliveryRepository->findPendingInspection();
    }

    /**
     * 获取待入库到货
     * @return PurchaseDelivery[]
     */
    public function getPendingWarehousingDeliveries(): array
    {
        return $this->deliveryRepository->findPendingWarehousing();
    }

    /**
     * 获取到货统计
     * @return array<string, mixed>
     */
    public function getDeliveryStatistics(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        return $this->deliveryRepository->getDeliveryStatistics($startDate, $endDate);
    }
}
