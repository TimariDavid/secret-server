<?php

namespace App\Tests\Entity;

use App\Entity\Secret;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecretTest extends WebTestCase
{
    public function testGetSerializerFormatWithXmlHeader(): void
    {
        $result = Secret::getSerializerFormat('application/xml');
        $this->assertEquals('xml', $result, 'The serializer format for "application/xml" should be "xml".');
    }

    public function testGetSerializerFormatWithYamlHeader(): void
    {
        $result = Secret::getSerializerFormat('application/x-yaml');
        $this->assertEquals('yaml', $result, 'The serializer format for "application/x-yaml" should be "yaml".');
    }

    public function testGetSerializerFormatWithUnknownHeader(): void
    {
        $result = Secret::getSerializerFormat('application/unknown');
        $this->assertEquals('json', $result, 'The serializer format for an unknown header should default to "json".');
    }

    public function testGetSerializerFormatWithEmptyHeader(): void
    {
        $result = Secret::getSerializerFormat('');
        $this->assertEquals('json', $result, 'The serializer format for an empty header should default to "json".');
    }

    public function testGetSerializerFormatWithNullHeader(): void
    {
        $result = Secret::getSerializerFormat(null);
        $this->assertEquals('json', $result, 'The serializer format for a null header should default to "json".');
    }
}
