<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class BorrowBookRequest
{
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^\d{6}$/',
        message: 'Borrower card number must contain exactly 6 digits.'
    )]
    public ?string $borrowerCardNumber = null;

    #[Assert\Type('string')]
    public ?string $expectedReturnAt = null;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();

        $request->borrowerCardNumber = isset($data['borrowerCardNumber'])
            ? (string) $data['borrowerCardNumber']
            : null;

        $request->expectedReturnAt = isset($data['expectedReturnAt'])
            ? (string) $data['expectedReturnAt']
            : null;

        return $request;
    }
}
