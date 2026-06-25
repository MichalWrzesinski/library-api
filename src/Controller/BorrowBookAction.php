<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\BorrowBookRequest;
use App\Entity\Book;
use App\Entity\Loan;
use App\Exception\BookAlreadyBorrowedException;
use App\Service\BookLoanService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
final readonly class BorrowBookAction
{
    public function __construct(
        private BookLoanService $bookLoanService,
        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Book $book, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON payload.');
        }

        $borrowBookRequest = BorrowBookRequest::fromArray($payload);
        $violations = $this->validator->validate($borrowBookRequest);

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                throw new BadRequestHttpException(sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage()));
            }
        }

        try {
            $loan = $this->bookLoanService->borrow(
                $book,
                (string) $borrowBookRequest->borrowerCardNumber,
                $this->parseExpectedReturnAt($borrowBookRequest->expectedReturnAt),
            );
        } catch (BookAlreadyBorrowedException $exception) {
            throw new ConflictHttpException($exception->getMessage(), $exception);
        }

        return new JsonResponse($this->normalizeLoan($loan), Response::HTTP_CREATED);
    }

    private function parseExpectedReturnAt(?string $expectedReturnAt): ?\DateTimeImmutable
    {
        if (null === $expectedReturnAt || '' === $expectedReturnAt) {
            return null;
        }

        try {
            return new \DateTimeImmutable($expectedReturnAt);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException('expectedReturnAt must be a valid date.', $exception);
        }
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
