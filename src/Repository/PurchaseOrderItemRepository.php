<?php

namespace Tourze\PurchaseManageBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\PurchaseManageBundle\Entity\PurchaseOrderItem;
use Tourze\PurchaseManageBundle\Enum\DeliveryStatus;

/**
 * @extends ServiceEntityRepository<PurchaseOrderItem>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: PurchaseOrderItem::class)]
class PurchaseOrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseOrderItem::class);
    }

    /**
     * 查找待收货的订单项
     *
     * @return array<PurchaseOrderItem>
     */
    public function findPendingDelivery(): array
    {
        /** @var array<PurchaseOrderItem> */
        return $this->createQueryBuilder('poi')
            ->andWhere('poi.deliveryStatus IN (:statuses)')
            ->setParameter('statuses', [
                DeliveryStatus::PENDING,
                DeliveryStatus::SHIPPED,
                DeliveryStatus::IN_TRANSIT,
            ])
            ->orderBy('poi.expectedDeliveryDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据产品查找订单项
     *
     * @param int $spuId
     * @return array<PurchaseOrderItem>
     */
    public function findByProduct(int $spuId): array
    {
        /** @var array<PurchaseOrderItem> */
        return $this->createQueryBuilder('poi')
            ->join('poi.spu', 's')
            ->andWhere('s.id = :spuId')
            ->setParameter('spuId', $spuId)
            ->orderBy('poi.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取产品采购统计
     *
     * @param int $spuId
     * @return array<string, mixed>
     */
    public function getProductStatistics(int $spuId): array
    {
        /** @var array<string, mixed> */
        return $this->createQueryBuilder('poi')
            ->select([
                'SUM(poi.quantity) as totalQuantity',
                'AVG(poi.unitPrice) as avgPrice',
                'MIN(poi.unitPrice) as minPrice',
                'MAX(poi.unitPrice) as maxPrice',
                'COUNT(poi.id) as orderCount',
            ])
            ->join('poi.spu', 's')
            ->andWhere('s.id = :spuId')
            ->setParameter('spuId', $spuId)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function save(PurchaseOrderItem $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseOrderItem $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
