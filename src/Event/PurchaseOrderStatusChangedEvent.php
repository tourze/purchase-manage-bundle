<?php

namespace Tourze\PurchaseManageBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;

class PurchaseOrderStatusChangedEvent extends Event
{
    public const NAME = 'purchase_order.status_changed';

    public function __construct(
        private readonly PurchaseOrder $purchaseOrder,
        private readonly PurchaseOrderStatus $oldStatus,
        private readonly PurchaseOrderStatus $newStatus,
    ) {
    }

    public function getPurchaseOrder(): PurchaseOrder
    {
        return $this->purchaseOrder;
    }

    public function getOldStatus(): PurchaseOrderStatus
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): PurchaseOrderStatus
    {
        return $this->newStatus;
    }
}
