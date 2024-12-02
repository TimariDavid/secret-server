<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecretControllerTest extends WebTestCase
{
    public function testGetSecretReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/secret/55441c20-b579-4065-8e7b-e5281927101a', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGetSecretReturnsYaml(): void
    {
        $client = static::createClient();
        $client->request('GET', '/secret/55441c20-b579-4065-8e7b-e5281927101a', [], [], ['HTTP_ACCEPT' => 'application/x-yaml']);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/x-yaml');
        $this->assertStringContainsString('hash: 55441c20-b579-4065-8e7b-e5281927101a', $client->getResponse()->getContent());
    }

    public function testGetSecretReturnsWhenTheHashIsIncorrect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/secret/incorrect-hash', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $this->assertEquals('{"error":"Secret not found"}', $client->getResponse()->getContent(), 'The hash must be included in the database.');
    }

    public function testGetSecretReturnsWhenTheRemainingViewsIsNull(): void
    {
        $client = static::createClient();
        $client->request('GET', '/secret/131e0cb1-d323-4723-87db-6f2538ac7c22', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $this->assertEquals('{"error":"Secret has no remaining views"}', $client->getResponse()->getContent(), 'The secret must be remaining views.');
    }

    public function testGetSecretReturnsWhenTheSecretIsExpired(): void
    {
        $client = static::createClient();
        $client->request('GET', '/secret/6bedd6c0-93f4-4076-9bbf-ed3eb97779f5', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $this->assertEquals('{"error":"Secret has expired"}', $client->getResponse()->getContent(), 'The secret must be remaining views.');
    }

    public function testPostSecret(): void
    {
        $client = static::createClient();
        $data = [
            'secretText' => 'This is a test secret',
            'expireAfterViews' => 10,
            'expireAfter' => 60
        ];

        $client->request(
            'POST',
            '/secret',
            $data
        );

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('hash', $responseData);
        $this->assertSame('This is a test secret', $responseData['secretText']);
    }
}