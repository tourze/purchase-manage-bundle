<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\ProductCoreBundle\Entity\Sku;
use Tourze\ProductCoreBundle\Entity\Spu;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrderItem;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;

#[AdminCrud(routePath: '/purchase/order-item', routeName: 'purchase_order_item')]
final class PurchaseOrderItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PurchaseOrderItem::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('采购订单项')
            ->setEntityLabelInPlural('采购订单项管理')
            ->setPageTitle('index', '采购订单项管理')
            ->setPageTitle('new', '创建采购订单项')
            ->setPageTitle('edit', '编辑采购订单项')
            ->setPageTitle('detail', '采购订单项详情')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined()
            ->setSearchFields(['productName', 'productCode', 'specification', 'purchaseOrder.orderNumber', 'purchaseOrder.title'])
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
            ->setTimezone('Asia/Shanghai')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield AssociationField::new('purchaseOrder', '采购订单')
            ->setRequired(true)
            ->setHelp('选择关联的采购订单')
            ->setColumns(6)
            ->formatValue(function ($value) {
                return $value instanceof PurchaseOrder ? $value->getOrderNumber() . ' - ' . $value->getTitle() : '';
            })
            ->autocomplete()
        ;

        yield AssociationField::new('spu', 'SPU商品')
            ->setHelp('选择标准产品单位')
            ->setColumns(6)
            ->formatValue(function ($value) {
                return $value instanceof Spu ? $value->getTitle() : '';
            })
            ->autocomplete()
            ->hideOnIndex()
        ;

        yield AssociationField::new('sku', 'SKU商品')
            ->setHelp('选择具体商品规格')
            ->setColumns(6)
            ->formatValue(function ($value) {
                return $value instanceof Sku ? $value->getGtin() . ' - ' . $value->getTitle() : '';
            })
            ->autocomplete()
            ->hideOnIndex()
        ;

        yield TextField::new('productName', '产品名称')
            ->setRequired(true)
            ->setHelp('商品名称')
            ->setColumns(4)
        ;

        yield TextField::new('productCode', '产品编码')
            ->setHelp('商品编码或条码')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield TextField::new('specification', '产品规格')
            ->setHelp('商品规格说明')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield NumberField::new('quantity', '采购数量')
            ->setRequired(true)
            ->setHelp('采购数量')
            ->setNumDecimals(4)
            ->setColumns(3)
        ;

        yield TextField::new('unit', '单位')
            ->setRequired(true)
            ->setHelp('商品计量单位')
            ->setColumns(2)
            ->hideOnIndex()
        ;

        yield MoneyField::new('unitPrice', '单价')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(4)
            ->setHelp('商品单价')
            ->setColumns(3)
        ;

        yield MoneyField::new('subtotal', '小计金额')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('数量×单价的小计')
            ->setColumns(4)
            ->hideOnForm()
        ;

        yield NumberField::new('taxRate', '税率(%)')
            ->setHelp('商品税率百分比')
            ->setNumDecimals(2)
            ->setColumns(3)
            ->hideOnIndex()
        ;

        yield MoneyField::new('taxAmount', '税额')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('商品税额')
            ->setColumns(3)
            ->hideOnIndex()
        ;

        yield ChoiceField::new('deliveryStatus', '到货状态')
            ->setChoices(fn () => DeliveryStatus::cases())
            ->setHelp('商品到货状态')
            ->setColumns(3)
            ->renderAsBadges([
                DeliveryStatus::PENDING->value => 'secondary',
                DeliveryStatus::SHIPPED->value => 'primary',
                DeliveryStatus::IN_TRANSIT->value => 'info',
                DeliveryStatus::ARRIVED->value => 'warning',
                DeliveryStatus::RECEIVED->value => 'light',
                DeliveryStatus::INSPECTED->value => 'dark',
                DeliveryStatus::WAREHOUSED->value => 'success',
            ])
        ;

        yield NumberField::new('receivedQuantity', '已收数量')
            ->setHelp('实际收货数量')
            ->setNumDecimals(4)
            ->setColumns(3)
            ->hideOnIndex()
        ;

        yield NumberField::new('qualifiedQuantity', '合格数量')
            ->setHelp('检验合格的数量')
            ->setNumDecimals(4)
            ->setColumns(3)
            ->hideOnIndex()
        ;

        yield DateField::new('expectedDeliveryDate', '期望交货日期')
            ->setHelp('期望供应商交货的日期')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield TextareaField::new('remark', '备注')
            ->setHelp('订单项相关备注信息')
            ->setNumOfRows(3)
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setColumns(3)
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(3)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('productName', '产品名称'))
            ->add(TextFilter::new('productCode', '产品编码'))
            ->add(EntityFilter::new('purchaseOrder', '采购订单'))
            ->add(ChoiceFilter::new('deliveryStatus', '到货状态')
                ->setChoices(array_flip(DeliveryStatus::getItem())))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('expectedDeliveryDate', '期望交货日期'))
        ;
    }
}
