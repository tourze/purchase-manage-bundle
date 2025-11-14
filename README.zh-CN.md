# 采购管理 Bundle

[English](README.md) | [中文](README.zh-CN.md)

一个全面的 Symfony Bundle，提供采购管理功能，包含订单管理、审批流程和交付跟踪能力。

## 功能特性

- **采购订单管理**：创建、更新和跟踪具有唯一订单号的采购订单
- **供应商集成**：与 `SupplierManageBundle` 无缝集成
- **审批工作流**：内置的采购订单审批流程，支持多级审批
- **交付跟踪**：跟踪采购交付和收货确认，包含物流状态
- **EasyAdmin 集成**：现成的管理界面，基于 EasyAdmin 4.x
- **审计跟踪**：完整的审计跟踪，包含用户跟踪和 IP 记录
- **工作流支持**：Symfony Workflow 集成，支持复杂的审批流程
- **数据完整性**：使用 Doctrine ORM 确保数据一致性和完整性
- **事件驱动**：丰富的事件系统支持业务扩展

## 核心实体

该 Bundle 提供以下核心实体：

### PurchaseOrder（采购订单）
- 唯一订单号（使用雪花算法生成）
- 订单状态（草稿、已提交、已批准、已拒绝、已完成等）
- 供应商关联
- 订单总额和明细
- 创建时间、更新时间等审计字段

### PurchaseOrderItem（采购订单项）
- 关联采购订单
- 产品信息（与 `ProductCoreBundle` 集成）
- 数量和单价
- 小计金额

### PurchaseApproval（采购审批）
- 关联采购订单和审批人
- 审批状态（待审批、已批准、已拒绝）
- 审批意见和时间
- 支持多级审批流程

### PurchaseDelivery（采购交付）
- 关联采购订单
- 交付状态（待发货、已发货、已送达、已签收等）
- 物流跟踪号
- 交付时间和确认信息

## 枚举类型

- `PurchaseOrderStatus` - 采购订单状态
- `ApprovalStatus` - 审批状态
- `DeliveryStatus` - 交付状态

## 安装

```bash
composer require tourze/purchase-manage-bundle
```

## 配置

在您的 `config/bundles.php` 中注册该 Bundle：

```php
return [
    // ...
    Tourze\PurchaseManageBundle\PurchaseManageBundle::class => ['all' => true],
];
```

## 依赖关系

该 Bundle 需要以下依赖：

### 核心依赖
- `DoctrineBundle` - 数据库 ORM 支持
- `ProductCoreBundle` - 产品核心功能集成
- `SupplierManageBundle` - 供应商管理集成
- `EasyAdminMenuBundle` - 管理后台菜单集成

### 扩展依赖
- `tourze/doctrine-snowflake-bundle` - 雪花算法 ID 生成
- `tourze/doctrine-timestamp-bundle` - 时间戳字段支持
- `tourze/doctrine-track-bundle` - 审计跟踪功能
- `tourze/doctrine-user-bundle` - 用户关联支持
- `tourze/doctrine-ip-bundle` - IP 记录功能
- `symfony/workflow` - 工作流支持
- `easycorp/easyadmin-bundle` - 管理界面支持

### 开发依赖
- `phpunit/phpunit` - 单元测试
- `phpstan/phpstan` - 静态分析

## 使用方法

### 基础采购订单创建

```php
<?php

use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;

// 创建新的采购订单
$purchaseOrder = new PurchaseOrder();
$purchaseOrder->setTitle('办公用品采购');
$purchaseOrder->setSupplier($supplier);
$purchaseOrder->setStatus(PurchaseOrderStatus::DRAFT);

// 向订单添加项目
$purchaseOrderItem = new PurchaseOrderItem();
$purchaseOrderItem->setProduct($product);
$purchaseOrderItem->setQuantity(10);
$purchaseOrderItem->setUnitPrice(25.50);

$purchaseOrder->addItem($purchaseOrderItem);

// 保存订单
$entityManager->persist($purchaseOrder);
$entityManager->flush();
```

### 审批工作流

```php
<?php

use Tourze\PurchaseManageBundle\Entity\PurchaseApproval;
use Tourze\PurchaseManageBundle\Enum\ApprovalStatus;

// 创建审批记录
$approval = new PurchaseApproval();
$approval->setPurchaseOrder($purchaseOrder);
$approval->setApprover($user);
$approval->setStatus(ApprovalStatus::PENDING);
$approval->setComment('等待审批');

$entityManager->persist($approval);
$entityManager->flush();

// 审批操作
$approval->setStatus(ApprovalStatus::APPROVED);
$approval->setComment('办公用品采购已批准');
$approval->setApprovedAt(new \DateTime());

$entityManager->flush();

// 更新采购订单状态
$purchaseOrder->setStatus(PurchaseOrderStatus::APPROVED);
$entityManager->flush();
```

### 交付跟踪

```php
<?php

use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;

// 记录发货
$delivery = new PurchaseDelivery();
$delivery->setPurchaseOrder($purchaseOrder);
$delivery->setDeliveryDate(new \DateTime());
$delivery->setTrackingNumber('TRACK123456');
$delivery->setStatus(DeliveryStatus::SHIPPED);
$delivery->setCarrier('顺丰快递');

$entityManager->persist($delivery);
$entityManager->flush();

// 记录签收
$delivery->setStatus(DeliveryStatus::DELIVERED);
$delivery->setReceivedAt(new \DateTime());
$delivery->setReceiver('张三');

$entityManager->flush();
```

## 配置选项

您可以使用 YAML 或 XML 配置该 Bundle：

```yaml
# config/packages/purchase_manage.yaml
purchase_manage:
    # 默认审批工作流
    approval_required: true
    auto_approve_threshold: 1000.00

    # 通知设置
    notifications:
        email_enabled: true
        sms_enabled: false

    # 订单号生成
    order_number:
        prefix: 'PO'
        length: 10
```

## 可用服务

该 Bundle 提供以下服务：

- `Tourze\PurchaseManageBundle\Service\PurchaseOrderService` - 采购订单管理服务
- `Tourze\PurchaseManageBundle\Service\ApprovalService` - 审批流程管理服务
- `Tourze\PurchaseManageBundle\Service\DeliveryService` - 交付跟踪管理服务
- `Tourze\PurchaseManageBundle\Repository\PurchaseOrderRepository` - 采购订单仓储
- `Tourze\PurchaseManageBundle\Repository\PurchaseApprovalRepository` - 审批记录仓储
- `Tourze\PurchaseManageBundle\Repository\PurchaseDeliveryRepository` - 交付记录仓储
- `Tourze\PurchaseManageBundle\Repository\PurchaseOrderItemRepository` - 订单项仓储

### Repository 使用示例

```php
<?php

use Tourze\PurchaseManageBundle\Repository\PurchaseOrderRepository;

/** @var PurchaseOrderRepository $repository */
$repository = $entityManager->getRepository(PurchaseOrder::class);

// 根据状态查找订单
$pendingOrders = $repository->findByStatus(PurchaseOrderStatus::PENDING);

// 根据供应商查找订单
$supplierOrders = $repository->findBySupplier($supplier);

// 查找指定时间范围的订单
$recentOrders = $repository->findRecentOrders(new \DateTime('-30 days'));
```

## 数据库架构

该 Bundle 创建以下数据库表：

- `purchase_order` - 主采购订单
- `purchase_order_item` - 单个订单项目
- `purchase_delivery` - 交付记录
- `purchase_approval` - 审批记录

## 事件系统

该 Bundle 提供丰富的事件系统，支持业务逻辑扩展：

### 核心事件
- `Tourze\PurchaseManageBundle\Event\PurchaseOrderCreatedEvent` - 采购订单创建后触发
- `Tourze\PurchaseManageBundle\Event\PurchaseOrderUpdatedEvent` - 采购订单更新后触发
- `Tourze\PurchaseManageBundle\Event\PurchaseApprovalCreatedEvent` - 审批记录创建后触发
- `Tourze\PurchaseManageBundle\Event\PurchaseDeliveryCreatedEvent` - 交付记录创建后触发

### 事件监听示例

```php
<?php

use Tourze\PurchaseManageBundle\Event\PurchaseOrderCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: PurchaseOrderCreatedEvent::class, method: 'onPurchaseOrderCreated')]
class PurchaseOrderListener
{
    public function onPurchaseOrderCreated(PurchaseOrderCreatedEvent $event): void
    {
        $purchaseOrder = $event->getPurchaseOrder();

        // 发送通知
        // 记录日志
        // 触发其他业务逻辑
    }
}
```

## EasyAdmin 集成

该 Bundle 提供完整的 EasyAdmin 控制器：

- `PurchaseOrderCrudController` - 采购订单管理
- `PurchaseOrderItemCrudController` - 订单项管理
- `PurchaseApprovalCrudController` - 审批管理
- `PurchaseDeliveryCrudController` - 交付管理

### 自定义 EasyAdmin 配置

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        PurchaseOrder:
            class: Tourze\PurchaseManageBundle\Entity\PurchaseOrder
            controller: Tourze\PurchaseManageBundle\Controller\Admin\PurchaseOrderCrudController
            form:
                fields:
                    - { property: 'title', label: '订单标题' }
                    - { property: 'supplier', label: '供应商' }
                    - { property: 'status', label: '状态' }
                    - { property: 'items', label: '订单项目', type: 'collection' }
```

## 测试

### 运行测试

```bash
# 运行完整测试套件
composer run test

# 运行特定测试
vendor/bin/phpunit tests/Entity/PurchaseOrderTest.php

# 运行测试并生成覆盖率报告
vendor/bin/phpunit --coverage-html coverage

# 运行 PHPStan 静态分析
composer run analyse

# 运行所有质量检查
composer run quality
```

### 测试覆盖率

该 Bundle 包含以下测试类型：
- **单元测试**：Entity、Enum、Service 测试
- **集成测试**：Repository、Controller 测试
- **功能测试**：EasyAdmin 界面测试

## 性能优化

### 数据库索引建议
```sql
-- 采购订单表索引
CREATE INDEX idx_purchase_order_status ON purchase_order(status);
CREATE INDEX idx_purchase_order_supplier ON purchase_order(supplier_id);
CREATE INDEX idx_purchase_order_created_at ON purchase_order(created_at);

-- 审批记录表索引
CREATE INDEX idx_purchase_approval_order ON purchase_approval(purchase_order_id);
CREATE INDEX idx_purchase_approval_approver ON purchase_approval(approver_id);
```

### 查询优化
- 使用 Doctrine 的查询构建器进行复杂查询
- 对大表使用分页查询
- 合理使用关联加载（EAGER vs LAZY）

## 部署注意事项

### 数据库迁移
```bash
# 生成迁移文件
php bin/console doctrine:migrations:diff

# 执行迁移
php bin/console doctrine:migrations:migrate
```

### 缓存清理
```bash
# 清理生产环境缓存
php bin/console cache:clear --env=prod

# 预热缓存
php bin/console cache:warmup --env=prod
```

## 贡献

欢迎贡献！请遵循以下指南：

### 开发环境设置
```bash
# 克隆仓库
git clone https://github.com/tourze/purchase-manage-bundle.git
cd purchase-manage-bundle

# 安装依赖
composer install

# 运行测试确保环境正常
composer run test
```

### 提交规范
- 遵循 PSR-12 编码规范
- 添加适当的测试覆盖
- 更新相关文档
- 确保所有质量检查通过

## 许可证

该 Bundle 采用 MIT 许可证。详见 [LICENSE](LICENSE) 文件。

## 支持

如需支持和提问：

- 📋 创建 Issue：[GitHub Issues](https://github.com/tourze/purchase-manage-bundle/issues)
- 📖 查看文档：[docs/](docs/) 目录
- 🔍 搜索现有 Issue：查看已知问题和解决方案
- 📧 邮件支持：support@tourze.dev

## 更新日志

查看 [CHANGELOG.md](CHANGELOG.md) 了解详细的更改列表和版本历史。

### 主要版本
- **v1.0.0** - 初始版本，包含基础采购管理功能
- **v1.1.0** - 添加工作流支持和高级审批功能
- **v1.2.0** - 优化性能和添加更多事件支持
