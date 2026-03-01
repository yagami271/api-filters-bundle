<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class InvalidFilterException extends BadRequestHttpException
{
}
