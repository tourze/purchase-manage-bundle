<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\PurchaseManageBundle\PurchaseManageBundle;

/**
 * @internal
 */
#[CoversClass(PurchaseManageBundle::class)]
#[RunTestsInSeparateProcesses]
final class PurchaseManageBundleTest extends AbstractBundleTestCase
{
}
