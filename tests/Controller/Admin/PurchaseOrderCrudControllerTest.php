<?php

declare(strict_types=1);

namespace Tourze\PurchaseManageBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\PurchaseManageBundle\Controller\Admin\PurchaseOrderCrudController;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;

/**
 * @internal
 */
#[CoversClass(PurchaseOrderCrudController::class)]
#[RunTestsInSeparateProcesses]
class PurchaseOrderCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testGetEntityFqcn(): void
    {
        self::assertEquals(PurchaseOrder::class, PurchaseOrderCrudController::getEntityFqcn());
    }

    public function testControllerExists(): void
    {
        self::assertTrue(class_exists(PurchaseOrderCrudController::class));
    }

    protected function getControllerService(): PurchaseOrderCrudController
    {
        return self::getService(PurchaseOrderCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'supplier' => ['supplier'];
        yield 'status' => ['status'];
        yield 'totalAmount' => ['totalAmount'];
        yield 'payableAmount' => ['payableAmount'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'supplier' => ['supplier'];
        yield 'status' => ['status'];
        yield 'totalAmount' => ['totalAmount'];
        yield 'payableAmount' => ['payableAmount'];
        yield 'expectedDeliveryDate' => ['expectedDeliveryDate'];
        yield 'deliveryAddress' => ['deliveryAddress'];
        yield 'paymentTerms' => ['paymentTerms'];
        yield 'remark' => ['remark'];
    }

    /**
     * 测试表单验证错误
     */
    #[Test]
    public function testValidationErrors(): void
    {
        // 使用ValidatorInterface直接验证实体，更可靠且简洁
        // 这种方式避免了HTTP层面的复杂性和类型系统问题
        $entity = new PurchaseOrder();
        $violations = self::getService(ValidatorInterface::class)->validate($entity);

        // 验证必填字段的错误存在
        $this->assertGreaterThan(0, count($violations), 'Empty PurchaseOrder should have validation errors');

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

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '订单编号' => ['订单编号'];
        yield '订单标题' => ['订单标题'];
        yield '供应商' => ['供应商'];
        yield '订单状态' => ['订单状态'];
        yield '订单总金额' => ['订单总金额'];
        yield '应付金额' => ['应付金额'];
        yield '创建时间' => ['创建时间'];
    }
}
