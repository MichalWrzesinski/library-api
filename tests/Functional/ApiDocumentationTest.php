<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class ApiDocumentationTest extends FunctionalTestCase
{
    public function testApiIsAvailable(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api');

        self::assertResponseIsSuccessful();
    }
}
