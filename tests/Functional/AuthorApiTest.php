<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class AuthorApiTest extends FunctionalTestCase
{
    public function testItCreatesAuthor(): void
    {
        $client = self::createClient();

        $authorIri = $this->createAuthor($client);

        self::assertSame('/api/authors/1', $authorIri);
    }
}
