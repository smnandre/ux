<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\DesignTokens\Resolver;

use Symfony\UX\DesignTokens\Exception\RuntimeException;

/**
 * Loads raw DTCG document data from a URI.
 *
 * {@see TokenTreeBuilder} calls a loader whenever a document points outside
 * itself, through a cross-file `$ref` or through the sources a Resolver
 * document selects. The bundle ships {@see JsonDocumentLoader}, which reads
 * the local filesystem; decorate the service aliased on this interface to read
 * tokens from somewhere else, such as a package, an object store or an HTTP
 * endpoint.
 *
 * A loader resolves nothing. It returns the document as authored, references
 * included, and the resolver takes it from there.
 *
 * What a loader returns ends up in the resolved tree the bundle caches, so it
 * is expected to answer the same document for the same URI within one build.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
interface DocumentLoaderInterface
{
    /**
     * Read one DTCG document.
     *
     * The URI arrives already resolved against the document that referenced
     * it, so a loader never has to reason about relative paths.
     *
     * @param string $uri absolute file path or URL to a W3C DTCG JSON token file
     *
     * @return array<array-key, mixed> the decoded document, with its references left in place
     *
     * @throws RuntimeException when the URI cannot be read, or does not decode to a JSON object
     */
    public function load(string $uri): array;
}
