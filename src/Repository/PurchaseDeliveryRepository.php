<?php

namespace Tourze\PurchaseManageBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\PurchaseManageBundle\Entity\PurchaseDelivery;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;

/**
 * @extends ServiceEntityRepository<PurchaseDelivery>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: PurchaseDelivery::class)]
class PurchaseDeliveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseDelivery::class);
    }

    /**
     * 查找在途物流
     *
     * @return array<PurchaseDelivery>
     */
    public function findInTransit(): array
    {
        /** @var array<PurchaseDelivery> */
        return $this->createQueryBuilder('pd')
            ->andWhere('pd.status IN (:statuses)')
            ->setParameter('statuses', [
                DeliveryStatus::SHIPPED,
                DeliveryStatus::IN_TRANSIT,
            ])
            ->orderBy('pd.estimatedArrivalTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找待检验的到货
     *
     * @return array<PurchaseDelivery>
     */
    public function findPendingInspection(): array
    {
        /** @var array<PurchaseDelivery> */
        return $this->createQueryBuilder('pd')
            ->andWhere('pd.status = :status')
            ->andWhere('pd.inspectTime IS NULL')
            ->setParameter('status', DeliveryStatus::RECEIVED)
            ->orderBy('pd.receiveTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找待入库的到货
     *
     * @return array<PurchaseDelivery>
     */
    public function findPendingWarehousing(): array
    {
        /** @var array<PurchaseDelivery> */
        return $this->createQueryBuilder('pd')
            ->andWhere('pd.status = :status')
            ->andWhere('pd.warehouseTime IS NULL')
            ->setParameter('status', DeliveryStatus::INSPECTED)
            ->orderBy('pd.inspectTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定订单的到货记录
     *
     * @param int $orderId
     * @return array<PurchaseDelivery>
     */
    public function findByOrder(int $orderId): array
    {
        /** @var array<PurchaseDelivery> */
        return $this->createQueryBuilder('pd')
            ->join('pd.purchaseOrder', 'po')
            ->andWhere('po.id = :orderId')
            ->setParameter('orderId', $orderId)
            ->orderBy('pd.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取到货统计
     *
     * @param \DateTimeInterface|null $startDate
     * @param \DateTimeInterface|null $endDate
     * @return array<string, mixed>
     */
    public function getDeliveryStatistics(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        $platform = $this->getEntityManager()->getConnection()->getDatabasePlatform();

        if ($platform instanceof SQLitePlatform) {
            // SQLite 简化版本
            $qb = $this->createQueryBuilder('pd')
                ->select([
                    'COUNT(pd.id) as totalCount',
                    'pd.status',
                    'COUNT(pd.id) as statusCount',
                    'SUM(pd.deliveredQuantity) as totalDelivered',
                    'SUM(pd.qualifiedQuantity) as totalQualified',
                    'SUM(pd.rejectedQuantity) as totalRejected',
                    '0 as avgDeliveryDays',  // SQLite 简化处理
                ])
                ->groupBy('pd.status')
            ;
        } else {
            // MySQL 和其他数据库的完整版本
            $qb = $this->createQueryBuilder('pd')
                ->select([
                    'COUNT(pd.id) as totalCount',
                    'pd.status',
                    'COUNT(pd.id) as statusCount',
                    'SUM(pd.deliveredQuantity) as totalDelivered',
                    'SUM(pd.qualifiedQuantity) as totalQualified',
                    'SUM(pd.rejectedQuantity) as totalRejected',
                    'AVG(TIMESTAMPDIFF(DAY, pd.shipTime, pd.receiveTime)) as avgDeliveryDays',
                ])
                ->groupBy('pd.status')
            ;
        }

        if (null !== $startDate) {
            $qb->andWhere('pd.createTime >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (null !== $endDate) {
            $qb->andWhere('pd.createTime <= :endDate')
                ->setParameter('endDate', $endDate)
            ;
        }

        /** @var array<string, mixed> */
        return $qb->getQuery()->getResult();
    }

    public function save(PurchaseDelivery $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseDelivery $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
