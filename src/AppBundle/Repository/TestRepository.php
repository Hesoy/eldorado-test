<?php
declare(strict_types = 1);

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class TestRepository
 *
 * @author hesoy s.shmygol@gmail.com
 * @package AppBundle\Repository
 */
class TestRepository extends EntityRepository
{
    public function getTotalRows(): int
    {
        $qb = $this->createQueryBuilder('test')->select('COUNT(test.id)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}