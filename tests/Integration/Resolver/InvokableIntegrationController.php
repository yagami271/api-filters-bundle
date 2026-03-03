<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Resolver;

use Isma\ApiFiltersBundle\Attribute\ApiFilter;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use Isma\ApiFiltersBundle\ValueObject\FilterType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invokable', methods: ['GET'])]
final class InvokableIntegrationController
{
    #[ApiFilter(name: 'firstname', allowedTypes: [FilterType::Eq->value, FilterType::Like->value])]
    #[ApiFilter(name: 'age')]
    public function __invoke(Filters $filters): JsonResponse
    {
        return new JsonResponse([
            'count' => count($filters->filters),
            'filters' => array_map(fn (Filter $f) => [
                'name' => $f->name,
                'type' => $f->type,
                'value' => $f->value instanceof \BackedEnum ? $f->value->value : $f->value,
            ], $filters->filters),
        ]);
    }
}
