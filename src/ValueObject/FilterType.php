<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\ValueObject;

enum FilterType: string
{
    case Eq = 'eq';

    case Neq = 'neq';

    case Like = 'like';

    case Gt = 'gt';

    case Gte = 'gte';

    case Lt = 'lt';

    case Lte = 'lte';

    case IsNull = 'is_null';

    case StartWith = 'start_with';

    case EndWith = 'end_with';
}
