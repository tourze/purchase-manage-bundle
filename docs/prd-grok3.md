# 采购管理 Symfony Bundle 技术方案 PRD

## 1. 引言

### 1.1 目的

本产品需求文档（PRD）描述了一个名为 `ProcurementBundle` 的 Symfony Bundle，用于实现企业采购管理功能。该 Bundle
旨在提供模块化、可扩展的解决方案，满足采购订单管理、审批流程和到货跟踪的需求。

### 1.2 目标用户

- **采购人员**：创建和管理采购订单。
- **审批人员**（如经理、总监）：审批或拒绝采购订单。
- **库存管理人员**：跟踪采购项的到货并更新库存。

### 1.3 功能概述

- **采购订单管理**：支持采购订单的创建、编辑、删除和查看，集成库存检查。
- **采购审批流程**：通过 Symfony Workflow 组件实现多级审批流程，确保合规性。
- **采购到货跟踪**：跟踪采购项状态，自动更新库存。

## 2. 功能需求

### 2.1 采购订单管理

- **创建采购订单**：
    - 输入供应商信息（名称、联系方式）。
    - 添加采购项（产品、数量、单价）。
    - 设置预期交货日期。
    - 检查库存水平，提示是否需要采购。
- **编辑采购订单**：
    - 修改供应商、采购项、数量、价格或交货日期。
- **删除采购订单**：
    - 提供删除功能，需用户确认。
- **查看采购订单**：
    - 提供列表视图，支持按供应商、状态、日期等过滤和搜索。
    - 显示单个采购订单的详细信息，包括采购项和状态。

### 2.2 采购审批流程

- **审批级别**：
    - 支持配置多级审批（如经理、总监）。
    - 可通过配置文件定义审批角色。
- **审批操作**：
    - 审批人员可批准或拒绝采购订单。
    - 提供通知机制（如邮件或系统通知）告知相关人员。
- **状态管理**：
    - 使用 Symfony Workflow 组件管理状态：
        - **草稿（draft）**：初始状态。
        - **待审批（pending_approval）**：提交审批后。
        - **已批准（approved）**：审批通过。
        - **已拒绝（rejected）**：审批未通过。
    - 转换：
        - **提交审批（submit）**：从草稿到待审批。
        - **批准（approve）**：从待审批到已批准。
        - **拒绝（reject）**：从待审批到已拒绝。

### 2.3 采购到货跟踪

- **状态跟踪**：
    - 每个采购项具有状态：已下单（ordered）、已发货（shipped）、已收货（received）。
    - 支持手动更新状态。
- **库存更新**：
    - 当采购项状态变为“已收货”时，自动更新产品库存。
- **集成**：
    - 可选支持与物流系统集成，获取实时跟踪信息（如快递单号）。

## 3. 非功能需求

- **性能**：
    - 系统应支持高效查询和更新，处理大量采购订单时响应时间小于 1 秒。
- **安全性**：
    - 仅授权用户可执行创建、编辑、删除或审批操作。
    - 使用 Symfony Security 组件和自定义 Voter 控制权限。
- **可扩展性**：
    - 支持添加新功能，如更多审批级别或第三方系统集成。
- **兼容性**：
    - 兼容 Symfony 5.x 和 6.x 版本。

## 4. 技术架构

### 4.1 实体设计

以下为主要实体及其字段：

| 实体名                   | 字段            | 描述                               |
|-----------------------|---------------|----------------------------------|
| **PurchaseOrder**     | id            | 主键                               |
|                       | supplier      | 多对一关系，关联 Supplier                |
|                       | items         | 一对多关系，关联 PurchaseOrderItem       |
|                       | status        | 审批状态（由 Workflow 管理）              |
|                       | createdAt     | 创建时间                             |
|                       | updatedAt     | 更新时间                             |
| **Supplier**          | id            | 主键                               |
|                       | name          | 供应商名称                            |
|                       | contactInfo   | 联系信息                             |
| **PurchaseOrderItem** | id            | 主键                               |
|                       | purchaseOrder | 多对一关系，关联 PurchaseOrder           |
|                       | product       | 多对一关系，关联 Product                 |
|                       | quantity      | 数量                               |
|                       | price         | 单价                               |
|                       | status        | 履行状态（ordered, shipped, received） |
| **Product**           | id            | 主键                               |
|                       | name          | 产品名称                             |
|                       | description   | 描述                               |
|                       | stockLevel    | 库存水平                             |

### 4.2 服务

- **PurchaseOrderService**：处理采购订单的创建、更新、删除和查询逻辑。
- **ApprovalWorkflowService**：管理审批流程，调用 Workflow 组件。
- **InventoryService**：处理库存更新，与 Product 实体交互。

### 4.3 控制器

- **PurchaseOrderController**：
    - 处理列表、创建、编辑、删除等 HTTP 请求。
    - 路由示例：`/purchase-order/list`, `/purchase-order/create`。
- **ApprovalController**：
    - 处理提交审批、批准、拒绝等操作。
    - 路由示例：`/purchase-order/{id}/submit`, `/purchase-order/{id}/approve`。

### 4.4 表单

- **PurchaseOrderType**：用于创建和编辑采购订单，支持动态添加采购项。
- **ApprovalType**：用于审批操作，提供批准/拒绝选项。

### 4.5 模板

- 使用 Twig 模板，提供以下页面：
    - 采购订单列表：`list.html.twig`
    - 采购订单表单：`form.html.twig`
    - 审批页面：`approval.html.twig`
- 模板支持覆盖，允许用户自定义界面。

### 4.6 工作流配置

- 使用 Symfony Workflow 组件定义审批流程：
  ```yaml
  framework:
      workflows:
          purchase_order_approval:
              type: workflow
              supports: [App\ProcurementBundle\Entity\PurchaseOrder]
              initial_marking: draft
              places:
                  - draft
                  - pending_approval
                  - approved
                  - rejected
              transitions:
                  submit:
                      from: draft
                      to: pending_approval
                  approve:
                      from: pending_approval
                      to: approved
                  reject:
                      from: pending_approval
                      to: rejected
              marking_store:
                  type: single_state
                  property: status
  ```
- 支持通过 `config/packages/procurement.yaml` 自定义工作流。

### 4.7 安全

- **访问控制**：
    - 使用 Symfony Security 组件和自定义 `PurchaseOrderVoter` 控制权限。
    - 示例权限逻辑：
        - **SUBMIT**：仅创建者可在“草稿”状态提交。
        - **APPROVE/REJECT**：仅具有 `ROLE_MANAGER` 或 `ROLE_DIRECTOR` 角色的用户可在“待审批”状态操作。

## 5. Symfony 集成

- **Bundle 结构**：
    - 命名空间：`App\ProcurementBundle`
    - 目录结构：
      ```
      ProcurementBundle/
      ├── Entity/
      ├── Service/
      ├── Controller/
      ├── Form/
      ├── Resources/
      │   ├── config/
      │   ├── views/
      │   ├── translations/
      ├── DependencyInjection/
      └── ProcurementBundle.php
      ```
- **配置**：
    - 用户可通过 `config/packages/procurement.yaml` 自定义工作流、角色等。
    - 示例配置：
      ```yaml
      procurement:
          security:
              approvers: [ROLE_MANAGER, ROLE_DIRECTOR]
      ```
- **依赖**：
    - Symfony 核心组件：Doctrine、Form、Workflow、Security。
    - 可选：VichUploaderBundle（文件上传）、Symfony Notifier（通知）。

## 6. 开发计划

| 阶段    | 任务                    | 时间估算 |
|-------|-----------------------|------|
| 研究与规划 | 定义实体、设计工作流、规划 UI/UX   | 1 周  |
| 开发    | 实现实体、服务、控制器、表单、模板     | 3 周  |
| 测试    | 单元测试、集成测试、用户接受测试      | 2 周  |
| 文档    | 编写安装和配置指南             | 1 周  |
| 发布    | 通过 Composer 分发 Bundle | 1 周  |

## 7. 测试

- **单元测试**：使用 PHPUnit 测试服务和实体逻辑。
- **集成测试**：验证工作流状态转换和控制器行为。
- **用户接受测试**：确保功能满足用户需求。

## 8. 部署

- **安装**：
  ```bash
  composer require app/procurement-bundle
  ```
- **启用**：
  在 `config/bundles.php` 中添加：
  ```php
  return [
      // ...
      App\ProcurementBundle\ProcurementBundle::class => ['all' => true],
  ];
  ```
- **配置**：
  在 `config/packages/procurement.yaml` 中设置工作流和权限。

## 9. 参考资料

- [Symfony Workflow Component](https://symfony.com/doc/current/components/workflow.html)
- [Symfony Bundle 最佳实践](https://symfony.com/doc/current/bundles/best_practices.html)
