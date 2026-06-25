<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class BookLoanApiTest extends FunctionalTestCase
{
    public function testItBorrowsAvailableBook(): void
    {
        $client = self::createClient();

        $bookIri = $this->createBook($client);
        $bookId = $this->extractIdFromIri($bookIri);

        $client->request('POST', sprintf('/api/books/%d/borrow', $bookId), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'borrowerCardNumber' => '654321',
            'expectedReturnAt' => '2026-07-25T00:00:00+00:00',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);

        $response = $this->decodeResponse($client);

        self::assertSame($bookId, $response['bookId']);
        self::assertSame('654321', $response['borrowerCardNumber']);
        self::assertIsString($response['borrowedAt']);
        self::assertSame('2026-07-25T00:00:00+00:00', $response['expectedReturnAt']);
        self::assertNull($response['returnedAt']);
    }

    public function testItDoesNotBorrowAlreadyBorrowedBook(): void
    {
        $client = self::createClient();

        $bookIri = $this->createBook($client);
        $bookId = $this->extractIdFromIri($bookIri);

        $this->borrowBook($client, $bookId, '654321');

        $client->request('POST', sprintf('/api/books/%d/borrow', $bookId), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'borrowerCardNumber' => '111111',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(409);
    }

    public function testItDoesNotBorrowBookWithInvalidBorrowerCardNumber(): void
    {
        $client = self::createClient();

        $bookIri = $this->createBook($client);
        $bookId = $this->extractIdFromIri($bookIri);

        $client->request('POST', sprintf('/api/books/%d/borrow', $bookId), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'borrowerCardNumber' => '12345',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(400);
    }

    public function testItDoesNotBorrowBookWithInvalidExpectedReturnAt(): void
    {
        $client = self::createClient();

        $bookIri = $this->createBook($client);
        $bookId = $this->extractIdFromIri($bookIri);

        $client->request('POST', sprintf('/api/books/%d/borrow', $bookId), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'borrowerCardNumber' => '654321',
            'expectedReturnAt' => 'invalid-date',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(400);
    }

    public function testItReturnsBorrowedBook(): void
    {
        $client = self::createClient();

        $bookIri = $this->createBook($client);
        $bookId = $this->extractIdFromIri($bookIri);

        $this->borrowBook($client, $bookId, '654321');

        $client->request('POST', sprintf('/api/books/%d/return', $bookId));

        self::assertResponseStatusCodeSame(200);

        $response = $this->decodeResponse($client);

        self::assertSame($bookId, $response['bookId']);
        self::assertSame('654321', $response['borrowerCardNumber']);
        self::assertIsString($response['returnedAt']);
    }

    public function testItDoesNotReturnAvailableBook(): void
    {
        $client = self::createClient();

        $bookIri = $this->createBook($client);
        $bookId = $this->extractIdFromIri($bookIri);

        $client->request('POST', sprintf('/api/books/%d/return', $bookId));

        self::assertResponseStatusCodeSame(400);
    }

    public function testItBorrowsBookAgainAfterReturn(): void
    {
        $client = self::createClient();

        $bookIri = $this->createBook($client);
        $bookId = $this->extractIdFromIri($bookIri);

        $this->borrowBook($client, $bookId, '654321');

        $client->request('POST', sprintf('/api/books/%d/return', $bookId));

        self::assertResponseStatusCodeSame(200);

        $this->borrowBook($client, $bookId, '111111');

        $client->request('POST', sprintf('/api/books/%d/borrow', $bookId), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'borrowerCardNumber' => '222222',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(409);
    }
}
