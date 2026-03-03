<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\ValueObject;

enum FilterType: string
{
    case Eq = 'eq';

    case Neq = 'neq';

    case Like = 'like';
}
