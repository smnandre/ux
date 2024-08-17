<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\LiveComponent\Tests\Fixtures\Factory;

use Symfony\UX\LiveComponent\Tests\Fixtures\Entity\Entity1;
use Symfony\UX\LiveComponent\Tests\Fixtures\Entity\ForeignKeyIdEntity;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

use Zenstruck\Foundry\Persistence\Proxy;
use function Zenstruck\Foundry\lazy;

/**
 * @extends PersistentProxyObjectFactory<ForeignKeyIdEntity>
 *
 * @method static ForeignKeyIdEntity|Proxy     createOne(array $attributes = [])
 * @method static ForeignKeyIdEntity[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static ForeignKeyIdEntity|Proxy     find(object|array|mixed $criteria)
 * @method static ForeignKeyIdEntity|Proxy     findOrCreate(array $attributes)
 * @method static ForeignKeyIdEntity|Proxy     first(string $sortedField = 'id')
 * @method static ForeignKeyIdEntity|Proxy     last(string $sortedField = 'id')
 * @method static ForeignKeyIdEntity|Proxy     random(array $attributes = [])
 * @method static ForeignKeyIdEntity|Proxy     randomOrCreate(array $attributes = []))
 * @method static ForeignKeyIdEntity[]|Proxy[] all()
 * @method static ForeignKeyIdEntity[]|Proxy[] findBy(array $attributes)
 * @method static ForeignKeyIdEntity[]|Proxy[] randomSet(int $number, array $attributes = []))
 * @method static ForeignKeyIdEntity[]|Proxy[] randomRange(int $min, int $max, array $attributes = []))
 */
class ForeignKeyIdEntityFactory extends PersistentProxyObjectFactory
{
    protected function defaults(): array|callable
    {
        return ['id' => lazy(static fn () => new Entity1())];
    }

    public static function class(): string
    {
        return ForeignKeyIdEntity::class;
    }
}
