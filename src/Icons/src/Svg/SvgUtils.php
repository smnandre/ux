<?php

namespace Symfony\UX\Icons\Svg;

final class SvgUtils
{
    /**
     * Transforms a valid icon ID into an icon name.
     *
     * @throws \InvalidArgumentException if the ID is not valid
     * @see isValidId()
     */
    public static function idToName(string $id): string
    {
        if (!self::isValidId($id)) {
            throw new \InvalidArgumentException(sprintf('The id "%s" is not a valid id.', $id));
        }

        return str_replace('--', ':', $id);
    }

    /**
     * Transforms a valid icon name into an ID.
     *
     * @throws \InvalidArgumentException if the name is not valid
     * @see isValidName()
     */
    public static function nameToId(string $name): string
    {
        if (!self::isValidName($name)) {
            throw new \InvalidArgumentException(sprintf('The name "%s" is not a valid name.', $name));
        }

        return str_replace(':', '--', $name);
    }

    /**
     * Returns whether the given string is a valid icon ID.
     *
     * An icon ID is a string that contains only lowercase letters, numbers, and hyphens.
     * It must be composed of slugs separated by double hyphens.
     *
     * @see https://regex101.com/r/mmvl5t/1
     */
    public static function isValidId(string $id): bool
    {
        return (bool) preg_match('#^([a-z0-9]+(-[a-z0-9]+)*)(--[a-z0-9]+(-[a-z0-9]+)*)*$#', $id);
    }

    /**
     * Returns whether the given string is a valid icon name.
     *
     * An icon name is a string that contains only lowercase letters, numbers, and hyphens.
     * It must be composed of slugs separated by colons.
     *
     * @see https://regex101.com/r/Gh2Z9s/1
     */
    public static function isValidName(string $name): bool
    {
        return (bool) preg_match('#^([a-z0-9]+(-[a-z0-9]+)*)(:[a-z0-9]+(-[a-z0-9]+)*)*$#', $name);
    }
}
