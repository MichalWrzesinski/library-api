<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Loan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Loan>
 */
final class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    public function findActiveLoanForBook(Book $book): ?Loan
    {
        return $this->createQueryBuilder('loan')
            ->andWhere('loan.book = :book')
            ->andWhere('loan.returnedAt IS NULL')
            ->setParameter('book', $book)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
