<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        /** @var Connection $connection */
        $connection = self::getContainer()->get(Connection::class);

        $connection->executeStatement('TRUNCATE TABLE loan, book, author RESTART IDENTITY CASCADE');

        self::ensureKernelShutdown();
    }

    protected function createAuthor(KernelBrowser $client, string $name = 'Robert C. Martin'): string
    {
        $client->request('POST', '/api/authors', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => $name,
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);

        $response = $this->decodeResponse($client);

        return (string) $response['@id'];
    }

    protected function createBook(
        KernelBrowser $client,
        string $serialNumber = '123456',
        string $title = 'Clean Code',
    ): string {
        $authorIri = $this->createAuthor($client);

        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'serialNumber' => $serialNumber,
            'title' => $title,
            'author' => $authorIri,
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);

        $response = $this->decodeResponse($client);

        return (string) $response['@id'];
    }

    protected function borrowBook(KernelBrowser $client, int $bookId, string $borrowerCardNumber): void
    {
        $client->request('POST', sprintf('/api/books/%d/borrow', $bookId), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'borrowerCardNumber' => $borrowerCardNumber,
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);
    }

    protected function extractIdFromIri(string $iri): int
    {
        return (int) basename($iri);
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeResponse(KernelBrowser $client): array
    {
        $content = $client->getResponse()->getContent();

        self::assertIsString($content);

        $decodedResponse = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($decodedResponse);

        return $decodedResponse;
    }
}
