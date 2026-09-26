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
 * Thrown when a document or a cache entry does not hold what it declared.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
class UnexpectedValueException extends \UnexpectedValueException implements ExceptionInterface
{
}
