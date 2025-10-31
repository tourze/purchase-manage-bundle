<?php

namespace Tourze\PurchaseManageBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum ApprovalStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait, SelectTrait {
        ItemTrait::toArray insteadof SelectTrait;
        ItemTrait::toSelectItem insteadof SelectTrait;
    }
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

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
            self::PENDING => '待审批',
            self::APPROVED => '已批准',
            self::REJECTED => '已拒绝',
            self::CANCELLED => '已取消',
        };
    }
}
