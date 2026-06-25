<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Loan;
use App\Exception\BookNotBorrowedException;
use App\Service\BookLoanService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
final readonly class ReturnBookAction
{
    public function __construct(
        private BookLoanService $bookLoanService,
    ) {
    }

    public function __invoke(Book $book): JsonResponse
    {
        try {
            $loan = $this->bookLoanService->returnBook($book);
        } catch (BookNotBorrowedException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        return new JsonResponse($this->normalizeLoan($loan));
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeLoan(Loan $loan): array
    {
        return [
            'id' => $loan->getId(),
            'bookId' => $loan->getBook()->getId(),
            'borrowerCardNumber' => $loan->getBorrowerCardNumber(),
            'borrowedAt' => $loan->getBorrowedAt()->format(\DateTimeImmutable::ATOM),
            'expectedReturnAt' => $loan->getExpectedReturnAt()?->format(\DateTimeImmutable::ATOM),
            'returnedAt' => $loan->getReturnedAt()?->format(\DateTimeImmutable::ATOM),
        ];
    }
}
