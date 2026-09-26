<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\DesignTokens\Importer;

use Symfony\UX\DesignTokens\Exception\InvalidArgumentException;

/**
 * Converts a document of another format into a DTCG token document.
 *
 * Implementations are autoconfigured with the "ux_design_tokens.importer" tag;
 * its "format" attribute names the format for ux:design-tokens:import.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
interface ImporterInterface
{
    /**
     * Import everything DTCG can represent, and leave the rest out.
     *
     * A value DTCG cannot hold is skipped, never approximated. An importer
     * that implements LoggerAwareInterface reports each skipped entry to its
     * logger. Only contents that cannot be read as this format at all are an
     * error.
     *
     * @param array<string, mixed> $context options of this import, which a format reads when it understands them
     *
     * @return array<string, mixed> a DTCG token document
     *
     * @throws InvalidArgumentException when the contents cannot be read as this format
     */
    public function import(string $contents, array $context = []): array;
}
