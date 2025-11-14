# Purchase Manage Bundle

[English](README.md) | [中文](README.zh-CN.md)

A comprehensive Symfony bundle for purchase management that provides order management, approval workflow, and delivery tracking capabilities.

## Features

- **Purchase Order Management**: Create, update, and track purchase orders with unique order numbers
- **Supplier Integration**: Seamless integration with supplier management
- **Approval Workflow**: Built-in approval process for purchase orders
- **Delivery Tracking**: Track purchase deliveries and receipt confirmations
- **EasyAdmin Integration**: Ready-to-use admin interface with EasyAdmin
- **Audit Trail**: Complete audit trail with user tracking and IP logging
- **Workflow Support**: Symfony Workflow integration for complex approval processes

## Installation

```bash
composer require tourze/purchase-manage-bundle
```

## Configuration

Register the bundle in your `config/bundles.php`:

```php
return [
    // ...
    Tourze\PurchaseManageBundle\PurchaseManageBundle::class => ['all' => true],
];
```

## Dependencies

This bundle requires the following bundles:

- `DoctrineBundle`
- `ProductCoreBundle`
- `SupplierManageBundle`
- `EasyAdminMenuBundle`

## Usage

### Basic Purchase Order Creation

```php
<?php

use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;

// Create a new purchase order
$purchaseOrder = new PurchaseOrder();
$purchaseOrder->setTitle('Office Supplies Purchase');
$purchaseOrder->setSupplier($supplier);
$purchaseOrder->setStatus(PurchaseOrderStatus::DRAFT);

// Add items to the order
$purchaseOrderItem = new PurchaseOrderItem();
$purchaseOrderItem->setProduct($product);
$purchaseOrderItem->setQuantity(10);
$purchaseOrderItem->setUnitPrice(25.50);

$purchaseOrder->addItem($purchaseOrderItem);

// Save the order
$entityManager->persist($purchaseOrder);
$entityManager->flush();
```

### Approval Workflow

```php
<?php

use Tourze\PurchaseManageBundle\Entity\PurchaseApproval;

// Create approval record
$approval = new PurchaseApproval();
$approval->setPurchaseOrder($purchaseOrder);
$approval->setApprover($user);
$approval->setStatus('approved');
$approval->setComment('Approved for office supplies');

$entityManager->persist($approval);
$entityManager->flush();

// Update purchase order status
$purchaseOrder->setStatus(PurchaseOrderStatus::APPROVED);
$entityManager->flush();
```

### Delivery Tracking

```php
<?php

use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;

// Record delivery
$delivery = new PurchaseDelivery();
$delivery->setPurchaseOrder($purchaseOrder);
$delivery->setDeliveryDate(new \DateTime());
$delivery->setTrackingNumber('TRACK123456');
$delivery->setStatus('delivered');

$entityManager->persist($delivery);
$entityManager->flush();
```

## Configuration Options

You can configure the bundle using YAML or XML configuration:

```yaml
# config/packages/purchase_manage.yaml
purchase_manage:
    # Default approval workflow
    approval_required: true
    auto_approve_threshold: 1000.00

    # Notification settings
    notifications:
        email_enabled: true
        sms_enabled: false

    # Order number generation
    order_number:
        prefix: 'PO'
        length: 10
```

## Available Services

The bundle provides the following services:

- `tourze_purchase_manage.order_manager` - Purchase order management
- `tourze_purchase_manage.approval_manager` - Approval workflow management
- `tourze_purchase_manage.delivery_manager` - Delivery tracking management
- `tourze_purchase_manage.notification_service` - Notification service

## Database Schema

The bundle creates the following database tables:

- `purchase_order` - Main purchase orders
- `purchase_order_item` - Individual order items
- `purchase_delivery` - Delivery records
- `purchase_approval` - Approval records

## Events

The bundle dispatches the following events:

- `purchase_order.created` - When a purchase order is created
- `purchase_order.updated` - When a purchase order is updated
- `purchase_approval.created` - When an approval is created
- `purchase_delivery.created` - When a delivery is recorded

## Testing

```bash
# Run the test suite
composer run test

# Run PHPStan analysis
composer run analyse

# Run all quality checks
composer run quality
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

## License

This bundle is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Support

For support and questions:

- Create an issue in the GitHub repository
- Check the [documentation](docs/)
- Review existing issues for solutions

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes and version history.