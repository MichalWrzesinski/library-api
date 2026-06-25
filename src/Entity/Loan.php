<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LoanRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    private Book $book;

    #[Assert\NotBlank]
    #[Assert\Regex('/^\d{6}$/')]
    #[ORM\Column(length: 6)]
    private ?string $borrowerCardNumber = null;

    #[ORM\Column]
    private \DateTimeImmutable $borrowedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expectedReturnAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $returnedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();

        $this->borrowedAt = $now;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): Book
    {
        return $this->book;
    }

    public function setBook(Book $book): static
    {
        $this->book = $book;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getBorrowerCardNumber(): ?string
    {
        return $this->borrowerCardNumber;
    }

    public function setBorrowerCardNumber(string $borrowerCardNumber): static
    {
        $this->borrowerCardNumber = $borrowerCardNumber;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getBorrowedAt(): \DateTimeImmutable
    {
        return $this->borrowedAt;
    }

    public function setBorrowedAt(\DateTimeImmutable $borrowedAt): static
    {
        $this->borrowedAt = $borrowedAt;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getExpectedReturnAt(): ?\DateTimeImmutable
    {
        return $this->expectedReturnAt;
    }

    public function setExpectedReturnAt(?\DateTimeImmutable $expectedReturnAt): static
    {
        $this->expectedReturnAt = $expectedReturnAt;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getReturnedAt(): ?\DateTimeImmutable
    {
        return $this->returnedAt;
    }

    public function setReturnedAt(?\DateTimeImmutable $returnedAt): static
    {
        $this->returnedAt = $returnedAt;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function markAsReturned(): void
    {
        $now = new \DateTimeImmutable();
        $this->returnedAt = $now;
        $this->updatedAt = $now;
    }
}
