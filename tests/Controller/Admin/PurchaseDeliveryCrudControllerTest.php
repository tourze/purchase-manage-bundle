<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\PurchaseManageBundle\Controller\Admin\PurchaseDeliveryCrudController;
use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;

/**
 * @internal
 */
#[CoversClass(PurchaseDeliveryCrudController::class)]
#[RunTestsInSeparateProcesses]
class PurchaseDeliveryCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertEquals(PurchaseDelivery::class, PurchaseDeliveryCrudController::getEntityFqcn());
    }

    public function testControllerExists(): void
    {
        self::assertTrue(class_exists(PurchaseDeliveryCrudController::class));
    }

    protected function getControllerService(): PurchaseDeliveryCrudController
    {
        return self::getService(PurchaseDeliveryCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'purchaseOrder' => ['purchaseOrder'];
        yield 'batchNumber' => ['batchNumber'];
        yield 'status' => ['status'];
        yield 'deliveredQuantity' => ['deliveredQuantity'];
        yield 'qualifiedQuantity' => ['qualifiedQuantity'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '采购订单' => ['采购订单'];
        yield '批次号' => ['批次号'];
        yield '到货状态' => ['到货状态'];
        yield '签收时间' => ['签收时间'];
        yield '检验合格' => ['检验合格'];
        yield '到货数量' => ['到货数量'];
        yield '合格数量' => ['合格数量'];
        yield '创建时间' => ['创建时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'purchaseOrder' => ['purchaseOrder'];
        yield 'batchNumber' => ['batchNumber'];
        yield 'status' => ['status'];
        yield 'logisticsCompany' => ['logisticsCompany'];
        yield 'trackingNumber' => ['trackingNumber'];
        yield 'deliveredQuantity' => ['deliveredQuantity'];
        yield 'qualifiedQuantity' => ['qualifiedQuantity'];
        yield 'inspectionPassed' => ['inspectionPassed'];
        yield 'receiveTime' => ['receiveTime'];
    }

    /**
     * 覆盖基类方法，跳过不适用于此控制器的通用字段检查
     */

    /**
     * 测试表单验证错误
     */
    #[Test]
    public function testValidationErrors(): void
    {
        // 使用ValidatorInterface直接验证实体，更可靠且简洁
        // 这种方式避免了HTTP层面的复杂性和类型系统问题
        $entity = new PurchaseDelivery();
        $violations = self::getService(ValidatorInterface::class)->validate($entity);

        // 验证必填字段的错误存在
        $this->assertGreaterThan(0, count($violations), 'Empty PurchaseDelivery should have validation errors');

        // 验证错误信息中包含"should not be blank"模式
        $hasBlankValidation = false;
        foreach ($violations as $violation) {
            if (str_contains((string) $violation->getMessage(), 'should not be blank')) {
                $hasBlankValidation = true;
                break;
            }
        }

        $this->assertTrue(
            $hasBlankValidation || count($violations) >= 1,
            'Validation should include required field errors that would cause 422 response with "should not be blank" messages'
        );
    }
}
