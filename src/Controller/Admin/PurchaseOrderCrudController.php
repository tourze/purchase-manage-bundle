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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;
use Tourze\SupplierManageBundle\Entity\Supplier;

#[AdminCrud(routePath: '/purchase/order', routeName: 'purchase_order')]
final class PurchaseOrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PurchaseOrder::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('采购订单')
            ->setEntityLabelInPlural('采购订单管理')
            ->setPageTitle('index', '采购订单管理')
            ->setPageTitle('new', '创建采购订单')
            ->setPageTitle('edit', '编辑采购订单')
            ->setPageTitle('detail', '采购订单详情')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined()
            ->setSearchFields(['orderNumber', 'title', 'supplier.name', 'deliveryAddress'])
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
            ->setTimezone('Asia/Shanghai')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('orderNumber', '订单编号')
            ->setHelp('系统自动生成，可手动修改')
            ->hideOnForm()
        ;

        yield TextField::new('title', '订单标题')
            ->setRequired(true)
            ->setHelp('请输入订单标题')
            ->setColumns(6)
        ;

        yield AssociationField::new('supplier', '供应商')
            ->setRequired(true)
            ->setHelp('选择供应商')
            ->setColumns(6)
            ->formatValue(function ($value) {
                return $value instanceof Supplier ? $value->getName() : '';
            })
            ->autocomplete()
        ;

        yield ChoiceField::new('status', '订单状态')
            ->setChoices(fn () => PurchaseOrderStatus::cases())
            ->setRequired(true)
            ->setHelp('选择订单状态')
            ->setColumns(4)
            ->renderAsBadges([
                PurchaseOrderStatus::DRAFT->value => 'secondary',
                PurchaseOrderStatus::PENDING_APPROVAL->value => 'warning',
                PurchaseOrderStatus::APPROVED->value => 'info',
                PurchaseOrderStatus::PURCHASING->value => 'primary',
                PurchaseOrderStatus::SHIPPED->value => 'light',
                PurchaseOrderStatus::RECEIVED->value => 'dark',
                PurchaseOrderStatus::COMPLETED->value => 'success',
                PurchaseOrderStatus::CANCELLED->value => 'danger',
                PurchaseOrderStatus::REJECTED->value => 'danger',
            ])
        ;

        yield MoneyField::new('totalAmount', '订单总金额')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('订单商品小计金额')
            ->setColumns(3)
        ;

        yield MoneyField::new('taxAmount', '税额')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('订单税额')
            ->setColumns(3)
            ->hideOnIndex()
        ;

        yield MoneyField::new('shippingAmount', '运费')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('订单运费')
            ->setColumns(3)
            ->hideOnIndex()
        ;

        yield MoneyField::new('discountAmount', '折扣金额')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('订单折扣金额')
            ->setColumns(3)
            ->hideOnIndex()
        ;

        yield MoneyField::new('payableAmount', '应付金额')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('实际应支付金额')
            ->setColumns(3)
        ;

        yield TextField::new('currency', '货币代码')
            ->setHelp('货币代码，如：CNY、USD')
            ->setColumns(2)
            ->hideOnIndex()
            ->hideOnForm()
        ;

        yield DateField::new('expectedDeliveryDate', '期望交货日期')
            ->setHelp('期望供应商交货的日期')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield DateField::new('actualDeliveryDate', '实际交货日期')
            ->setHelp('供应商实际交货日期')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield TextareaField::new('deliveryAddress', '收货地址')
            ->setHelp('订单收货地址')
            ->setNumOfRows(3)
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextField::new('paymentTerms', '付款条款')
            ->setHelp('付款条件和方式')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextareaField::new('remark', '订单备注')
            ->setHelp('订单相关备注信息')
            ->setNumOfRows(4)
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield DateTimeField::new('approveTime', '审批时间')
            ->setHelp('订单审批通过时间')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield IntegerField::new('approvedBy', '审批人ID')
            ->setHelp('审批人用户ID')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield TextareaField::new('approvalComment', '审批意见')
            ->setHelp('审批人的意见')
            ->setNumOfRows(3)
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield DateTimeField::new('cancelTime', '取消时间')
            ->setHelp('订单取消时间')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield TextareaField::new('cancelReason', '取消原因')
            ->setHelp('订单取消原因说明')
            ->setNumOfRows(3)
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield CollectionField::new('items', '订单项')
            ->setHelp('订单包含的商品明细')
            ->onlyOnDetail()
            ->setTemplatePath('@PurchaseManage/admin/purchase_order/items.html.twig')
        ;

        yield CollectionField::new('approvals', '审批记录')
            ->setHelp('订单审批流程记录')
            ->onlyOnDetail()
            ->setTemplatePath('@PurchaseManage/admin/purchase_order/approvals.html.twig')
        ;

        yield CollectionField::new('deliveries', '到货记录')
            ->setHelp('订单到货物流记录')
            ->onlyOnDetail()
            ->setTemplatePath('@PurchaseManage/admin/purchase_order/deliveries.html.twig')
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
            ->add(TextFilter::new('orderNumber', '订单编号'))
            ->add(TextFilter::new('title', '订单标题'))
            ->add(EntityFilter::new('supplier', '供应商'))
            ->add(ChoiceFilter::new('status', '订单状态')
                ->setChoices(array_flip(PurchaseOrderStatus::getItem())))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('expectedDeliveryDate', '期望交货日期'))
            ->add(DateTimeFilter::new('actualDeliveryDate', '实际交货日期'))
        ;
    }
}
