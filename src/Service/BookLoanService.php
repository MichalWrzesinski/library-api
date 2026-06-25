<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Book;
use App\Entity\Loan;
use App\Exception\BookAlreadyBorrowedException;
use App\Exception\BookNotBorrowedException;
use App\Repository\LoanRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BookLoanService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoanRepositoryInterface $loanRepository,
    ) {
    }

    public function borrow(
        Book $book,
        string $borrowerCardNumber,
        ?\DateTimeImmutable $expectedReturnAt = null,
    ): Loan {
        $activeLoan = $this->loanRepository->findActiveLoanForBook($book);

        if ($activeLoan instanceof Loan) {
            throw new BookAlreadyBorrowedException('Book is already borrowed.');
        }

        $loan = new Loan();
        $loan->setBook($book);
        $loan->setBorrowerCardNumber($borrowerCardNumber);
        $loan->setExpectedReturnAt($expectedReturnAt);

        $this->entityManager->persist($loan);
        $this->entityManager->flush();

        return $loan;
    }

    public function returnBook(Book $book): Loan
    {
        $activeLoan = $this->loanRepository->findActiveLoanForBook($book);

        if (!$activeLoan instanceof Loan) {
            throw new BookNotBorrowedException('Book is not currently borrowed.');
        }

        $activeLoan->markAsReturned();

        $this->entityManager->flush();

        return $activeLoan;
    }
}
