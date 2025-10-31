<?php

namespace Tourze\PurchaseManageBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum PurchaseOrderStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait, SelectTrait {
        ItemTrait::toArray insteadof SelectTrait;
        ItemTrait::toSelectItem insteadof SelectTrait;
    }
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case PURCHASING = 'purchasing';
    case SHIPPED = 'shipped';
    case RECEIVED = 'received';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';

    /**
     * @return array<string, string>
     */
    public static function getItem(): array
    {
        $items = [];
        foreach (self::cases() as $case) {
            $items[$case->value] = $case->getLabel();
        }

        return $items;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => '草稿',
            self::PENDING_APPROVAL => '待审批',
            self::APPROVED => '已审批',
            self::PURCHASING => '采购中',
            self::SHIPPED => '已发货',
            self::RECEIVED => '已收货',
            self::COMPLETED => '已完成',
            self::CANCELLED => '已取消',
            self::REJECTED => '已拒绝',
        };
    }
}
