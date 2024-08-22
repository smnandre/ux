<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Map;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class MapFactory
{
    public static function fromArray(array $array): Map
    {
        $map = new Map();

        $map->fitBoundsToMarkers();

        if ($center = $array['center'] ?? null) {
            if (!\is_array($center)) {
                throw new \InvalidArgumentException('The "center" parameter must be an array.');
            }

            $map->center(self::createPoint($center));
            unset($array['center']);
        }

        if ($zoom = $array['zoom'] ?? null) {
            if (!is_numeric($zoom)) {
                throw new \InvalidArgumentException('The "zoom" parameter must be numeric.');
            }

            $map->fitBoundsToMarkers(false);
            $map->zoom((float)$array['zoom']);
            unset($array['zoom']);
        }

        if (isset($array['markers'])) {
            if (!\is_array($array['markers'])) {
                throw new \InvalidArgumentException('The "markers" parameter must be an array.');
            }
            foreach ($array['markers'] as $marker) {
                if (!\is_array($marker)) {
                    throw new \InvalidArgumentException('The "markers" parameter must be an array of arrays.');
                }
                $marker = self::createMarker($marker);
                $map = $map->addMarker($marker);
            }
            unset($array['markers']);
        }

        if (\count($array) > 0) {
            throw new \InvalidArgumentException(\sprintf('Unknown map parameters: %s', implode(', ', array_keys($array))));
        }

        return $map;
    }

    private static function createMarker(array $marker): Marker
    {
        if ($infoWindow = $marker['infoWindow'] ?? null) {
            if (!\is_array($infoWindow)) {
                throw new \InvalidArgumentException('The "infoWindow" parameter must be an array.');
            }

            $infoWindow = new InfoWindow(
                headerContent: $infoWindow['headerContent'] ?? null,
                content: $infoWindow['content'] ?? null,
            );
        }

        if (isset($marker['title']) && !\is_string($marker['title'])) {
            throw new \InvalidArgumentException('The "title" parameter must be a string.');
        }

        if (isset($marker['extra']) && !\is_array($marker['extra'])) {
            throw new \InvalidArgumentException('The "extra" parameter must be an array.');
        }

        return new Marker(
            self::createPoint($marker),
            $marker['title'] ?? null,
            $infoWindow,
            $marker['extra'] ?? [],
        );
    }

    private static function createPoint(array $point): Point
    {
        // TODO: array list: 0, 1
        // TODO array keys: lat, lng

        if (!isset($point['lat']) || !isset($point['lng'])) {
            throw new \InvalidArgumentException('The "center" parameter must be an array with "lat" and "lng" keys.');
        }
        if (!is_numeric($point['lat']) || !is_numeric($point['lng'])) {
            throw new \InvalidArgumentException('The "lat" and "lng" keys of the "center" parameter must be numeric.');
        }

        return new Point($point['lat'], $point['lng']);
    }
}
