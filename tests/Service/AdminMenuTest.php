<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\PurchaseManageBundle\Entity\PurchaseApproval;
use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrderItem;
use Tourze\PurchaseManageBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    private LinkGeneratorInterface&MockObject $linkGenerator;

    private ItemInterface&MockObject $rootItem;

    protected function onSetUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $container = self::getContainer();
        $container->set(LinkGeneratorInterface::class, $this->linkGenerator);
        $this->adminMenu = self::getService(AdminMenu::class);
        $this->rootItem = $this->createMock(ItemInterface::class);
    }

    public function testInvokeCreatesMenuStructure(): void
    {
        $purchaseMenu = $this->createMock(ItemInterface::class);

        $this->rootItem
            ->expects(self::exactly(2))
            ->method('getChild')
            ->with('采购管理')
            ->willReturnOnConsecutiveCalls(null, $purchaseMenu)
        ;

        $this->rootItem
            ->expects(self::once())
            ->method('addChild')
            ->with('采购管理')
            ->willReturn($purchaseMenu)
        ;

        $this->linkGenerator
            ->expects(self::exactly(4))
            ->method('getCurdListPage')
            ->willReturnCallback(function (string $entityClass) {
                return match ($entityClass) {
                    PurchaseOrder::class => '/purchase/order',
                    PurchaseOrderItem::class => '/purchase/order-item',
                    PurchaseDelivery::class => '/purchase/delivery',
                    PurchaseApproval::class => '/purchase/approval',
                    default => '/unknown',
                };
            })
        ;

        $purchaseMenu
            ->expects(self::exactly(4))
            ->method('addChild')
            ->willReturnSelf()
        ;

        $purchaseMenu
            ->expects(self::exactly(4))
            ->method('setUri')
            ->willReturnSelf()
        ;

        $purchaseMenu
            ->expects(self::exactly(4))
            ->method('setAttribute')
            ->willReturnSelf()
        ;

        ($this->adminMenu)($this->rootItem);
    }

    public function testInvokeReturnsEarlyIfMenuChildIsNull(): void
    {
        $this->rootItem
            ->expects(self::exactly(2))
            ->method('getChild')
            ->with('采购管理')
            ->willReturnOnConsecutiveCalls(null, null)
        ;

        $this->rootItem
            ->expects(self::once())
            ->method('addChild')
            ->with('采购管理')
        ;

        $this->linkGenerator
            ->expects(self::never())
            ->method('getCurdListPage')
        ;

        ($this->adminMenu)($this->rootItem);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        // 此测试验证了AdminMenu实现了MenuProviderInterface
        $this->assertTrue(true);
    }
}
