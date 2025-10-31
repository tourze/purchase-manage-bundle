<?php

namespace Tourze\PurchaseManageBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum DeliveryStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait, SelectTrait {
        ItemTrait::toArray insteadof SelectTrait;
        ItemTrait::toSelectItem insteadof SelectTrait;
    }
    case PENDING = 'pending';
    case SHIPPED = 'shipped';
    case IN_TRANSIT = 'in_transit';
    case ARRIVED = 'arrived';
    case RECEIVED = 'received';
    case INSPECTED = 'inspected';
    case WAREHOUSED = 'warehoused';

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
            self::PENDING => '待发货',
            self::SHIPPED => '已发货',
            self::IN_TRANSIT => '运输中',
            self::ARRIVED => '已到达',
            self::RECEIVED => '已收货',
            self::INSPECTED => '已检验',
            self::WAREHOUSED => '已入库',
        };
    }
}
