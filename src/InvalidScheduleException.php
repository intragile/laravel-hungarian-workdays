<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays;

use InvalidArgumentException;

/**
 * Thrown when work schedule data is malformed or internally inconsistent.
 */
final class InvalidScheduleException extends InvalidArgumentException {}
