<?php

namespace Symfony\UX\TwigComponent\Tests\Unit\Metadata;

use PHPUnit\Framework\TestCase;

use Symfony\UX\TwigComponent\Metadata\ComponentPropsMetadataFactory;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

class ComponentPropsMetadataFactoryTest extends TestCase
{

    public function testCreate()
    {
        $factory = new ComponentPropsMetadataFactory();

        $metadata = $factory->create(ComponentWithExposedProps::class);

        $this->assertCount(2, $metadata);

        return $metadata;
    }
}


class ComponentWithExposedProps
{
    #[ExposeInTemplate]
    public string $name;

    #[ExposeInTemplate]
    public bool $isMethod;
}