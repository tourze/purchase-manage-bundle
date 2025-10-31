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
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\PurchaseManageBundle\Entity\PurchaseApproval;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\ApprovalStatus;

#[AdminCrud(routePath: '/purchase/approval', routeName: 'purchase_approval')]
final class PurchaseApprovalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PurchaseApproval::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('采购审批记录')
            ->setEntityLabelInPlural('采购审批记录管理')
            ->setPageTitle('index', '采购审批记录管理')
            ->setPageTitle('new', '创建审批记录')
            ->setPageTitle('edit', '编辑审批记录')
            ->setPageTitle('detail', '审批记录详情')
            ->setDefaultSort(['sequence' => 'ASC', 'id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined()
            ->setSearchFields(['level', 'approverName', 'approverRole', 'purchaseOrder.orderNumber', 'purchaseOrder.title'])
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
            ->setHelp('选择需要审批的采购订单')
            ->setColumns(6)
            ->formatValue(function ($value) {
                return $value instanceof PurchaseOrder ? $value->getOrderNumber() . ' - ' . $value->getTitle() : '';
            })
            ->autocomplete()
        ;

        yield TextField::new('level', '审批级别')
            ->setRequired(true)
            ->setHelp('审批层级名称，如：部门主管、财务主管等')
            ->setColumns(4)
        ;

        yield IntegerField::new('sequence', '审批顺序')
            ->setRequired(true)
            ->setHelp('审批流程中的执行顺序')
            ->setColumns(2)
        ;

        yield ChoiceField::new('status', '审批状态')
            ->setChoices(fn () => ApprovalStatus::cases())
            ->setRequired(true)
            ->setHelp('当前审批状态')
            ->setColumns(3)
            ->renderAsBadges([
                ApprovalStatus::PENDING->value => 'warning',
                ApprovalStatus::APPROVED->value => 'success',
                ApprovalStatus::REJECTED->value => 'danger',
                ApprovalStatus::CANCELLED->value => 'secondary',
            ])
        ;

        yield IntegerField::new('approverId', '审批人ID')
            ->setHelp('审批人的用户ID')
            ->setColumns(3)
            ->hideOnIndex()
        ;

        yield TextField::new('approverName', '审批人姓名')
            ->setHelp('审批人姓名')
            ->setColumns(4)
        ;

        yield TextField::new('approverRole', '审批角色')
            ->setHelp('审批人在组织中的角色')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield TextareaField::new('comment', '审批意见')
            ->setHelp('审批人的意见和建议')
            ->setNumOfRows(4)
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield DateTimeField::new('approveTime', '审批时间')
            ->setHelp('实际审批操作时间')
            ->setColumns(4)
        ;

        yield MoneyField::new('amountLimit', '审批金额限制')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('该审批人的金额审批权限')
            ->setColumns(4)
            ->hideOnIndex()
        ;

        yield ArrayField::new('attachments', '附件')
            ->setHelp('审批相关的附件文档')
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield BooleanField::new('requireCountersign', '需要会签')
            ->setHelp('是否需要多人会签审批')
            ->setColumns(2)
            ->renderAsSwitch(false)
            ->hideOnIndex()
        ;

        yield TextareaField::new('remark', '备注')
            ->setHelp('审批记录相关备注')
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
            ->add(TextFilter::new('level', '审批级别'))
            ->add(EntityFilter::new('purchaseOrder', '采购订单'))
            ->add(ChoiceFilter::new('status', '审批状态')
                ->setChoices(array_flip(ApprovalStatus::getItem())))
            ->add(TextFilter::new('approverName', '审批人姓名'))
            ->add(TextFilter::new('approverRole', '审批角色'))
            ->add(BooleanFilter::new('requireCountersign', '需要会签'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('approveTime', '审批时间'))
        ;
    }
}
