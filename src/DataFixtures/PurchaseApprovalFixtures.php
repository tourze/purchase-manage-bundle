<?php

namespace Tourze\PurchaseManageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\PurchaseManageBundle\Entity\PurchaseApproval;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\ApprovalStatus;

class PurchaseApprovalFixtures extends Fixture implements DependentFixtureInterface
{
    public const APPROVAL_REFERENCE = 'approval';

    public function load(ObjectManager $manager): void
    {
        $approvals = [
            [
                'orderIndex' => 0,
                'status' => ApprovalStatus::APPROVED,
                'comment' => '符合采购标准，批准采购',
                'approverName' => '审批人A',
            ],
            [
                'orderIndex' => 1,
                'status' => ApprovalStatus::PENDING,
                'comment' => '待审批',
                'approverName' => '审批人B',
            ],
            [
                'orderIndex' => 2,
                'status' => ApprovalStatus::REJECTED,
                'comment' => '预算超支，暂不批准',
                'approverName' => '审批人C',
            ],
        ];

        foreach ($approvals as $index => $approvalData) {
            $approval = new PurchaseApproval();

            $order = $this->getReference(PurchaseOrderFixtures::ORDER_REFERENCE . '_' . $approvalData['orderIndex'], PurchaseOrder::class);
            $approval->setPurchaseOrder($order);
            $approval->setLevel((string) ($index + 1));
            $approval->setSequence($index + 1);
            $approval->setStatus($approvalData['status']);
            $approval->setComment($approvalData['comment']);
            $approval->setApproverName($approvalData['approverName']);
            $approval->setApproverRole('ROLE_ADMIN');
            $approval->setApproverId($index + 1);
            $approval->setAmountLimit('10000.00');
            $approval->setApproveTime(new \DateTimeImmutable());

            $manager->persist($approval);
            $this->addReference(self::APPROVAL_REFERENCE . '_' . $index, $approval);
        }

        $manager->flush();
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            PurchaseOrderFixtures::class,
        ];
    }
}
