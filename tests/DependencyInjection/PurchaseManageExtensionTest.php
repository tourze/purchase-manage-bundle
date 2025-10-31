<?php

namespace Tourze\PurchaseManageBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\PurchaseManageBundle\DependencyInjection\PurchaseManageExtension;

/**
 * @internal
 */
#[CoversClass(PurchaseManageExtension::class)]
class PurchaseManageExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private PurchaseManageExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new PurchaseManageExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testLoadWithDefaultValues(): void
    {
        $this->extension->load([], $this->container);

        $this->assertTrue($this->container->hasParameter('purchase_manage.approval_levels'));
        $this->assertTrue($this->container->hasParameter('purchase_manage.default_currency'));
        $this->assertTrue($this->container->hasParameter('purchase_manage.default_tax_rate'));

        $this->assertEquals([], $this->container->getParameter('purchase_manage.approval_levels'));
        $this->assertEquals('CNY', $this->container->getParameter('purchase_manage.default_currency'));
        $this->assertEquals(0.13, $this->container->getParameter('purchase_manage.default_tax_rate'));
    }

    public function testLoadWithEnvironmentVariables(): void
    {
        $_ENV['PURCHASE_DEFAULT_CURRENCY'] = 'USD';
        $_ENV['PURCHASE_DEFAULT_TAX_RATE'] = '0.08';

        $this->extension->load([], $this->container);

        $this->assertEquals('USD', $this->container->getParameter('purchase_manage.default_currency'));
        $this->assertEquals(0.08, $this->container->getParameter('purchase_manage.default_tax_rate'));

        unset($_ENV['PURCHASE_DEFAULT_CURRENCY'], $_ENV['PURCHASE_DEFAULT_TAX_RATE']);
    }

    public function testGetAlias(): void
    {
        $this->assertEquals('purchase_manage', $this->extension->getAlias());
    }

    public function testPrepend(): void
    {
        // 注册twig扩展以触发prepend逻辑
        $this->container->registerExtension(new \Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension());

        $this->extension->prepend($this->container);

        // 验证twig配置被正确预置
        $twigConfig = $this->container->getExtensionConfig('twig');
        $this->assertNotEmpty($twigConfig);

        // 验证配置结构：应该包含一个paths配置项
        $this->assertCount(1, $twigConfig);
        $config = $twigConfig[0];
        $this->assertArrayHasKey('paths', $config);

        // 验证路径配置：应该有一个PurchaseManage命名空间
        $paths = $config['paths'];
        $this->assertIsArray($paths);
        $this->assertContains('PurchaseManage', $paths, 'PurchaseManage namespace not found in twig paths');

        // 验证路径指向正确的目录
        $purchaseManagePath = array_search('PurchaseManage', $paths, true);
        $this->assertNotFalse($purchaseManagePath, 'PurchaseManage namespace path not found');
        $this->assertIsString($purchaseManagePath);
        $this->assertTrue(str_contains($purchaseManagePath, 'Resources/views'), 'PurchaseManage path does not contain expected directory structure');
    }

    public function testPrependWithoutTwigExtension(): void
    {
        // 不注册twig扩展，确保prepend方法提前返回
        $this->extension->prepend($this->container);

        // 验证没有twig配置被添加
        $twigConfig = $this->container->getExtensionConfig('twig');
        $this->assertEmpty($twigConfig);
    }
}
