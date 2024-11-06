<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ComponentMetadata
{
    private const DEFAULT_ATTRIBUTES_VAR = 'attributes';
    
    private readonly string $name;
    private readonly string $template;
    private readonly ?string $class;
    private readonly ?string $serviceId;
    private readonly bool $exposePublicProps;
    private readonly string $attributesVar;
    
    /**
     * @internal
     */
    public function __construct(
        private readonly array $config,
    ) {
        $this->serviceId = $config['service_id'] ?? '';
        $this->attributesVar = $config['attributes_var'] ?? self::DEFAULT_ATTRIBUTES_VAR;
    }

    public function getName(): string
    {
        return $this->name ??= $this->config['key'];
    }

    /**
     * @return string Component's twig template
     */
    public function getTemplate(): string
    {
        return $this->template ??= $this->config['template'];
    }

    /**
     * @return class-string The Component's FQCN
     */
    public function getClass(): string
    {
        return $this->class ??= $this->config['class'];
    }

    /**
     * @return string The Component's service id
     */
    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    public function isPublicPropsExposed(): bool
    {
        return $this->exposePublicProps ??= ($this->config['expose_public_props'] ?? false);
    }

    public function isAnonymous(): bool
    {
        return null === $this->class;
    }

    public function getAttributesVar(): string
    {
        return $this->attributesVar;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}
