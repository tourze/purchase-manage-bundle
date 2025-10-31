<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;

#[AdminCrud(routePath: '/purchase/delivery', routeName: 'purchase_delivery')]
final class PurchaseDeliveryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PurchaseDelivery::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('采购到货记录')
            ->setEntityLabelInPlural('采购到货记录管理')
            ->setPageTitle('index', '采购到货记录管理')
            ->setPageTitle('new', '创建到货记录')
            ->setPageTitle('edit', '编辑到货记录')
            ->setPageTitle('detail', '到货记录详情')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined()
            ->setSearchFields(['batchNumber', 'logisticsCompany', 'trackingNumber', 'purchaseOrder.orderNumber', 'purchaseOrder.title'])
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

        yield TextField::new('batchNumber', '批次号')
            ->setRequired(true)
            ->setHelp('到货批次编号')
            ->setColumns(4)
        ;

        yield ChoiceField::new('status', '到货状态')
            ->setChoices(fn () => DeliveryStatus::cases())
            ->setRequired(true)
            ->setHelp('当前到货状态')
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

        yield TextField::new('logisticsCompany', '物流公司')
            ->setHelp('承运物流公司名称')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield TextField::new('trackingNumber', '物流单号')
            ->setHelp('物流跟踪单号')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield DateTimeField::new('shipTime', '发货时间')
            ->setHelp('供应商发货时间')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield DateTimeField::new('estimatedArrivalTime', '预计到达时间')
            ->setHelp('预计货物到达时间')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield DateTimeField::new('actualArrivalTime', '实际到达时间')
            ->setHelp('货物实际到达时间')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield DateTimeField::new('receiveTime', '签收时间')
            ->setHelp('货物签收时间')
            ->setColumns(4)
        ;

        yield TextField::new('receivedBy', '签收人')
            ->setHelp('签收人姓名')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield DateTimeField::new('inspectTime', '检验时间')
            ->setHelp('货物检验时间')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield TextField::new('inspectedBy', '检验人')
            ->setHelp('检验人姓名')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield BooleanField::new('inspectionPassed', '检验合格')
            ->setHelp('检验是否合格')
            ->setColumns(2)
            ->renderAsSwitch(false)
        ;

        yield TextareaField::new('inspectionComment', '检验意见')
            ->setHelp('检验结果意见')
            ->setNumOfRows(3)
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield NumberField::new('deliveredQuantity', '到货数量')
            ->setHelp('实际到货数量')
            ->setNumDecimals(4)
            ->setColumns(3)
        ;

        yield NumberField::new('qualifiedQuantity', '合格数量')
            ->setHelp('检验合格数量')
            ->setNumDecimals(4)
            ->setColumns(3)
        ;

        yield NumberField::new('rejectedQuantity', '不合格数量')
            ->setHelp('检验不合格数量')
            ->setNumDecimals(4)
            ->setColumns(3)
            ->hideOnIndex()
        ;

        yield TextareaField::new('discrepancyReason', '差异处理说明')
            ->setHelp('数量或质量差异的处理说明')
            ->setNumOfRows(3)
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield DateTimeField::new('warehouseTime', '入库时间')
            ->setHelp('货物入库时间')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield TextField::new('warehousedBy', '入库人')
            ->setHelp('入库操作人员')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield TextField::new('warehouseLocation', '库位')
            ->setHelp('货物存放库位')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield ArrayField::new('attachments', '附件')
            ->setHelp('相关附件文档')
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield TextareaField::new('remark', '备注')
            ->setHelp('到货记录相关备注')
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

        yield IntegerField::new('createdBy', '创建人')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield IntegerField::new('updatedBy', '更新人')
            ->hideOnForm()
            ->hideOnIndex()
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
            ->add(TextFilter::new('batchNumber', '批次号'))
            ->add(EntityFilter::new('purchaseOrder', '采购订单'))
            ->add(ChoiceFilter::new('status', '到货状态')
                ->setChoices(array_flip(DeliveryStatus::getItem())))
            ->add(TextFilter::new('logisticsCompany', '物流公司'))
            ->add(TextFilter::new('trackingNumber', '物流单号'))
            ->add(BooleanFilter::new('inspectionPassed', '检验合格'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('receiveTime', '签收时间'))
            ->add(DateTimeFilter::new('warehouseTime', '入库时间'))
        ;
    }
}
