<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class BookApiTest extends FunctionalTestCase
{
    public function testItCreatesBook(): void
    {
        $client = self::createClient();

        $authorIri = $this->createAuthor($client);

        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'serialNumber' => '123456',
            'title' => 'Clean Code',
            'author' => $authorIri,
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);

        $response = $this->decodeResponse($client);

        self::assertSame('/api/books/1', $response['@id']);
        self::assertSame('123456', $response['serialNumber']);
        self::assertSame('Clean Code', $response['title']);
        self::assertIsArray($response['author']);
        self::assertSame('Robert C. Martin', $response['author']['name']);
    }

    public function testItDoesNotCreateBookWithInvalidSerialNumber(): void
    {
        $client = self::createClient();

        $authorIri = $this->createAuthor($client);

        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'serialNumber' => '12345',
            'title' => 'Invalid Book',
            'author' => $authorIri,
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(422);
    }

    public function testItDoesNotCreateBookWithDuplicatedSerialNumber(): void
    {
        $client = self::createClient();

        $this->createBook($client, '123456');

        $authorIri = $this->createAuthor($client, 'Martin Fowler');

        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'serialNumber' => '123456',
            'title' => 'Duplicate Book',
            'author' => $authorIri,
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(422);
    }

    public function testItDeletesBookWithoutLoanHistory(): void
    {
        $client = self::createClient();

        $bookIri = $this->createBook($client);
        $bookId = $this->extractIdFromIri($bookIri);

        $client->request('DELETE', sprintf('/api/books/%d', $bookId));

        self::assertResponseStatusCodeSame(204);
    }

    public function testItDoesNotDeleteBookWithLoanHistory(): void
    {
        $client = self::createClient();

        $bookIri = $this->createBook($client);
        $bookId = $this->extractIdFromIri($bookIri);

        $this->borrowBook($client, $bookId, '654321');

        $client->request('POST', sprintf('/api/books/%d/return', $bookId));

        self::assertResponseStatusCodeSame(200);

        $client->request('DELETE', sprintf('/api/books/%d', $bookId));

        self::assertResponseStatusCodeSame(409);
    }
}
