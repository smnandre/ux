<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\DesignTokens\Exception;

/**
 * Thrown when a reference names a token or group the merged tree does not
 * define.
 *
 * A document a Resolver completes with other sources can hold such a
 * reference legitimately, which is what tells this failure apart from the
 * other reference errors.
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class UnresolvedReferenceException extends RuntimeException
{
}
