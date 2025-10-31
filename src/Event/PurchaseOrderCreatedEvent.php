<?php

namespace Tourze\PurchaseManageBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;

class PurchaseOrderCreatedEvent extends Event
{
    public const NAME = 'purchase_order.created';

    public function __construct(
        private readonly PurchaseOrder $purchaseOrder,
    ) {
    }

    public function getPurchaseOrder(): PurchaseOrder
    {
        return $this->purchaseOrder;
    }
}
