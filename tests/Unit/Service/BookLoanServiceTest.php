<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Book;
use App\Entity\Loan;
use App\Exception\BookAlreadyBorrowedException;
use App\Exception\BookNotBorrowedException;
use App\Repository\LoanRepositoryInterface;
use App\Service\BookLoanService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class BookLoanServiceTest extends TestCase
{
    public function testItBorrowsAvailableBook(): void
    {
        $book = new Book();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $loanRepository = $this->createMock(LoanRepositoryInterface::class);

        $loanRepository
            ->expects(self::once())
            ->method('findActiveLoanForBook')
            ->with($book)
            ->willReturn(null);

        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(Loan::class));

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = new BookLoanService($entityManager, $loanRepository);

        $expectedReturnAt = new \DateTimeImmutable('2026-07-25T00:00:00+00:00');

        $loan = $service->borrow($book, '654321', $expectedReturnAt);

        self::assertSame($book, $loan->getBook());
        self::assertSame('654321', $loan->getBorrowerCardNumber());
        self::assertSame($expectedReturnAt, $loan->getExpectedReturnAt());
        self::assertNull($loan->getReturnedAt());
    }

    public function testItDoesNotBorrowAlreadyBorrowedBook(): void
    {
        $book = new Book();
        $activeLoan = new Loan();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $loanRepository = $this->createMock(LoanRepositoryInterface::class);

        $loanRepository
            ->expects(self::once())
            ->method('findActiveLoanForBook')
            ->with($book)
            ->willReturn($activeLoan);

        $entityManager
            ->expects(self::never())
            ->method('persist');

        $entityManager
            ->expects(self::never())
            ->method('flush');

        $service = new BookLoanService($entityManager, $loanRepository);

        $this->expectException(BookAlreadyBorrowedException::class);

        $service->borrow($book, '654321');
    }

    public function testItReturnsBorrowedBook(): void
    {
        $book = new Book();

        $activeLoan = new Loan();
        $activeLoan->setBook($book);
        $activeLoan->setBorrowerCardNumber('654321');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $loanRepository = $this->createMock(LoanRepositoryInterface::class);

        $loanRepository
            ->expects(self::once())
            ->method('findActiveLoanForBook')
            ->with($book)
            ->willReturn($activeLoan);

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = new BookLoanService($entityManager, $loanRepository);

        $returnedLoan = $service->returnBook($book);

        self::assertSame($activeLoan, $returnedLoan);
        self::assertNotNull($returnedLoan->getReturnedAt());
    }

    public function testItDoesNotReturnAvailableBook(): void
    {
        $book = new Book();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $loanRepository = $this->createMock(LoanRepositoryInterface::class);

        $loanRepository
            ->expects(self::once())
            ->method('findActiveLoanForBook')
            ->with($book)
            ->willReturn(null);

        $entityManager
            ->expects(self::never())
            ->method('flush');

        $service = new BookLoanService($entityManager, $loanRepository);

        $this->expectException(BookNotBorrowedException::class);

        $service->returnBook($book);
    }
}
