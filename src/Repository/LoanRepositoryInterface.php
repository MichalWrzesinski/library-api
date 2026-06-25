<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Loan;

interface LoanRepositoryInterface
{
    public function findActiveLoanForBook(Book $book): ?Loan;

    public function existsForBook(Book $book): bool;
}
