<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Metadata;

use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class ComponentPropsMetadataFactory
{
    /**
     * @var array<string, ComponentPropsMetadata>
     */
    private static array $cache = [];

    public function create(string $componentClass): ComponentPropsMetadata
    {
        if ($metadata = self::$cache[$componentClass] ?? null) {
            return $metadata;
        }

        $metadata = new ComponentPropsMetadata();
        $reflectionClass = new \ReflectionClass($componentClass);

        // Extract properties with #[ExposeInTemplate]
        foreach ($reflectionClass->getProperties() as $property) {
            if (!$attribute = $property->getAttributes(ExposeInTemplate::class)[0] ?? null) {
                continue;
            }

            /** @var ExposeInTemplate $attributeInstance */
            $attributeInstance = $attribute->newInstance();
            $metadata->add(
                $attributeInstance->name ?? $property->name,
                 $attributeInstance->getter,
            );
        }

        // Extract :heverload:
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (!$attribute = $method->getAttributes(ExposeInTemplate::class)[0] ?? null) {
                continue;
            }

            if ($method->getNumberOfRequiredParameters()) {
                throw new \LogicException(sprintf('Cannot use %s on methods with required parameters (%s::%s).', ExposeInTemplate::class, $componentClass, $method->name));
            }

            /** @var ExposeInTemplate $attributeInstance */
            $attributeInstance = $attribute->newInstance();

            $metadata->add(
                $attributeInstance->name ?? (str_starts_with($method->name, 'get') ? lcfirst(substr($method->name, 3)) : $method->name),
                    $method->name,
            );
        }

        return self::$cache[$componentClass] = $metadata;
    }

}
