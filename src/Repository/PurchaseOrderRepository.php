<?php

namespace Tourze\PurchaseManageBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrder;
use Tourze\PurchaseManageBundle\Enum\PurchaseOrderStatus;

/**
 * @extends ServiceEntityRepository<PurchaseOrder>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: PurchaseOrder::class)]
class PurchaseOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseOrder::class);
    }

    /**
     * 查找待审批的订单
     *
     * @return array<PurchaseOrder>
     */
    public function findPendingApproval(): array
    {
        /** @var array<PurchaseOrder> */
        return $this->createQueryBuilder('po')
            ->andWhere('po.status = :status')
            ->setParameter('status', PurchaseOrderStatus::PENDING_APPROVAL)
            ->orderBy('po.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定状态的订单
     *
     * @param PurchaseOrderStatus $status
     * @return array<PurchaseOrder>
     */
    public function findByStatus(PurchaseOrderStatus $status): array
    {
        /** @var array<PurchaseOrder> */
        return $this->createQueryBuilder('po')
            ->andWhere('po.status = :status')
            ->setParameter('status', $status)
            ->orderBy('po.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定供应商的订单
     *
     * @param int $supplierId
     * @param array<string> $statuses
     * @return array<PurchaseOrder>
     */
    public function findBySupplier(int $supplierId, array $statuses = []): array
    {
        $qb = $this->createQueryBuilder('po')
            ->andWhere('po.supplier = :supplier')
            ->setParameter('supplier', $supplierId)
        ;

        if ([] !== $statuses) {
            $qb->andWhere('po.status IN (:statuses)')
                ->setParameter('statuses', $statuses)
            ;
        }

        /** @var array<PurchaseOrder> */
        return $qb->orderBy('po.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定日期范围的订单
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return array<PurchaseOrder>
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var array<PurchaseOrder> */
        return $this->createQueryBuilder('po')
            ->andWhere('po.createTime BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('po.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 搜索订单
     *
     * @param array<string, mixed> $criteria
     * @return array<PurchaseOrder>
     */
    public function searchOrders(array $criteria): array
    {
        $qb = $this->createQueryBuilder('po')
            ->leftJoin('po.supplier', 's')
            ->leftJoin('po.items', 'i')
        ;

        $this->applySearchCriteria($qb, $criteria);

        /** @var array<PurchaseOrder> */
        return $qb->orderBy('po.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 应用搜索条件
     *
     * @param QueryBuilder $qb
     * @param array<string, mixed> $criteria
     */
    private function applySearchCriteria(QueryBuilder $qb, array $criteria): void
    {
        $this->applyKeywordFilter($qb, $criteria);
        $this->applySupplierFilter($qb, $criteria);
        $this->applyStatusFilter($qb, $criteria);
        $this->applyAmountFilters($qb, $criteria);
        $this->applyDateFilters($qb, $criteria);
    }

    /**
     * 应用关键词过滤
     *
     * @param QueryBuilder $qb
     * @param array<string, mixed> $criteria
     */
    private function applyKeywordFilter(QueryBuilder $qb, array $criteria): void
    {
        if (isset($criteria['keyword']) && is_string($criteria['keyword'])) {
            $keyword = '%' . $criteria['keyword'] . '%';
            $qb->andWhere('po.orderNumber LIKE :keyword OR po.title LIKE :keyword')
                ->setParameter('keyword', $keyword)
            ;
        }
    }

    /**
     * 应用供应商过滤
     *
     * @param QueryBuilder $qb
     * @param array<string, mixed> $criteria
     */
    private function applySupplierFilter(QueryBuilder $qb, array $criteria): void
    {
        if (isset($criteria['supplier'])) {
            $qb->andWhere('s.id = :supplier')
                ->setParameter('supplier', $criteria['supplier'])
            ;
        }
    }

    /**
     * 应用状态过滤
     *
     * @param QueryBuilder $qb
     * @param array<string, mixed> $criteria
     */
    private function applyStatusFilter(QueryBuilder $qb, array $criteria): void
    {
        if (!isset($criteria['status'])) {
            return;
        }

        $status = $this->normalizeStatus($criteria['status']);
        if (null !== $status) {
            $qb->andWhere('po.status = :status')
                ->setParameter('status', $status)
            ;
        }
    }

    /**
     * 标准化状态值
     *
     * @param mixed $status
     */
    private function normalizeStatus($status): ?PurchaseOrderStatus
    {
        if ($status instanceof PurchaseOrderStatus) {
            return $status;
        }

        if (is_string($status)) {
            return PurchaseOrderStatus::tryFrom($status);
        }

        return null;
    }

    /**
     * 应用金额过滤
     *
     * @param QueryBuilder $qb
     * @param array<string, mixed> $criteria
     */
    private function applyAmountFilters(QueryBuilder $qb, array $criteria): void
    {
        if (isset($criteria['minAmount'])) {
            $qb->andWhere('po.totalAmount >= :minAmount')
                ->setParameter('minAmount', $criteria['minAmount'])
            ;
        }

        if (isset($criteria['maxAmount'])) {
            $qb->andWhere('po.totalAmount <= :maxAmount')
                ->setParameter('maxAmount', $criteria['maxAmount'])
            ;
        }
    }

    /**
     * 应用日期过滤
     *
     * @param QueryBuilder $qb
     * @param array<string, mixed> $criteria
     */
    private function applyDateFilters(QueryBuilder $qb, array $criteria): void
    {
        if (isset($criteria['startDate'])) {
            $qb->andWhere('po.createTime >= :startDate')
                ->setParameter('startDate', $criteria['startDate'])
            ;
        }

        if (isset($criteria['endDate'])) {
            $qb->andWhere('po.createTime <= :endDate')
                ->setParameter('endDate', $criteria['endDate'])
            ;
        }
    }

    /**
     * 获取订单统计
     *
     * @param \DateTimeInterface|null $startDate
     * @param \DateTimeInterface|null $endDate
     * @return array<string, mixed>
     */
    public function getOrderStatistics(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        $qb = $this->createQueryBuilder('po')
            ->select([
                'COUNT(po.id) as totalOrders',
                'SUM(po.totalAmount) as totalAmount',
                'AVG(po.totalAmount) as avgAmount',
                'po.status',
                'COUNT(po.id) as statusCount',
            ])
            ->groupBy('po.status')
        ;

        if (null !== $startDate) {
            $qb->andWhere('po.createTime >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (null !== $endDate) {
            $qb->andWhere('po.createTime <= :endDate')
                ->setParameter('endDate', $endDate)
            ;
        }

        /** @var array<string, mixed> */
        return $qb->getQuery()->getResult();
    }

    /**
     * 获取待处理订单数量
     *
     * @return int
     */
    public function countPendingOrders(): int
    {
        return (int) $this->createQueryBuilder('po')
            ->select('COUNT(po.id)')
            ->andWhere('po.status IN (:statuses)')
            ->setParameter('statuses', [
                PurchaseOrderStatus::DRAFT,
                PurchaseOrderStatus::PENDING_APPROVAL,
                PurchaseOrderStatus::APPROVED,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function save(PurchaseOrder $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseOrder $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
