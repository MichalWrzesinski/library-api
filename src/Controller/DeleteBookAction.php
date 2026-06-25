<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Exception\BookHasLoansException;
use App\Service\BookDeletionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

#[AsController]
final readonly class DeleteBookAction
{
    public function __construct(
        private BookDeletionService $bookDeletionService,
    ) {
    }

    public function __invoke(Book $book): Response
    {
        try {
            $this->bookDeletionService->delete($book);
        } catch (BookHasLoansException $exception) {
            throw new ConflictHttpException($exception->getMessage(), $exception);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
