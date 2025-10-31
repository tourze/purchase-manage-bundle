<?php

namespace Tourze\PurchaseManageBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class PurchaseManageExtension extends AutoExtension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        parent::load($configs, $container);
        $this->configureParameters($configs, $container);
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('twig')) {
            return;
        }

        // 注册 @PurchaseManage 命名空间以加载 Bundle 内置模板
        $container->prependExtensionConfig('twig', [
            'paths' => [
                __DIR__ . '/../Resources/views' => 'PurchaseManage',
            ],
        ]);
    }

    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }

    /**
     * @param array<mixed> $configs
     */
    protected function configureParameters(array $configs, ContainerBuilder $container): void
    {
        $defaultCurrency = $_ENV['PURCHASE_DEFAULT_CURRENCY'] ?? 'CNY';
        $defaultTaxRateEnv = $_ENV['PURCHASE_DEFAULT_TAX_RATE'] ?? '0.13';

        // 确保参数类型正确
        if (!is_string($defaultCurrency)) {
            $defaultCurrency = 'CNY';
        }

        if (!is_string($defaultTaxRateEnv)) {
            $defaultTaxRateEnv = '0.13';
        }

        $container->setParameter('purchase_manage.default_currency', $defaultCurrency);
        $container->setParameter('purchase_manage.default_tax_rate', (float) $defaultTaxRateEnv);
        $container->setParameter('purchase_manage.approval_levels', []);
    }
}
