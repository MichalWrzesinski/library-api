<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Book;
use App\Exception\BookHasLoansException;
use App\Repository\LoanRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BookDeletionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoanRepositoryInterface $loanRepository,
    ) {
    }

    public function delete(Book $book): void
    {
        if ($this->loanRepository->existsForBook($book)) {
            throw new BookHasLoansException('Book cannot be deleted because it has loan history.');
        }

        $this->entityManager->remove($book);
        $this->entityManager->flush();
    }
}
