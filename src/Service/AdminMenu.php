<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PurchaseManageBundle\Entity\PurchaseApproval;
use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrderItem;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('采购管理')) {
            $item->addChild('采购管理');
        }

        $purchaseMenu = $item->getChild('采购管理');
        if (null === $purchaseMenu) {
            return;
        }

        // 采购订单管理
        $purchaseMenu
            ->addChild('采购订单')
            ->setUri($this->linkGenerator->getCurdListPage(PurchaseOrder::class))
            ->setAttribute('icon', 'fas fa-file-invoice')
        ;

        // 订单项明细
        $purchaseMenu
            ->addChild('订单项明细')
            ->setUri($this->linkGenerator->getCurdListPage(PurchaseOrderItem::class))
            ->setAttribute('icon', 'fas fa-list-alt')
        ;

        // 到货管理
        $purchaseMenu
            ->addChild('到货管理')
            ->setUri($this->linkGenerator->getCurdListPage(PurchaseDelivery::class))
            ->setAttribute('icon', 'fas fa-truck')
        ;

        // 审批记录
        $purchaseMenu
            ->addChild('审批记录')
            ->setUri($this->linkGenerator->getCurdListPage(PurchaseApproval::class))
            ->setAttribute('icon', 'fas fa-check-circle')
        ;
    }
}
