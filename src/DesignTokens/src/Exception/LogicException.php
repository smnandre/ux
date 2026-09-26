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
 * Thrown when the package is used in a way its configuration does not allow.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
class LogicException extends \LogicException implements ExceptionInterface
{
}
