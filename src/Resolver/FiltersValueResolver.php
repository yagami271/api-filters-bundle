<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Resolver;

use Isma\ApiFiltersBundle\Attribute\ApiFilter;
use Isma\ApiFiltersBundle\Exception\InvalidFilterException;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class FiltersValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<Filters>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Filters::class !== $argument->getType()) {
            return [];
        }

        $controller = $request->attributes->get('_controller');
        $apiFilters = $this->readApiFilterAttributes($controller);

        /** @var array<string, mixed> $queryFilters */
        $queryFilters = $request->query->all('filters');

        if ([] === $apiFilters || [] === $queryFilters) {
            return [new Filters()];
        }

        $filters = [];
        foreach ($queryFilters as $fieldName => $types) {
            if (!\is_array($types)) {
                throw new InvalidFilterException(\sprintf('Invalid filter format for "%s". Expected filters[fieldName][typeName]=value.', $fieldName));
            }

            if (!isset($apiFilters[$fieldName])) {
                throw new InvalidFilterException(\sprintf('Unknown filter "%s".', $fieldName));
            }

            $apiFilter = $apiFilters[$fieldName];

            foreach ($types as $typeName => $value) {
                $typeName = (string) $typeName;
                $this->validateType($fieldName, $typeName, $apiFilter);
                $resolvedValue = $this->resolveValue($fieldName, $value, $apiFilter);
                $filters[] = new Filter($fieldName, $typeName, $resolvedValue);
            }
        }

        return [new Filters($filters)];
    }

    /**
     * @return array<string, ApiFilter>
     */
    private function readApiFilterAttributes(mixed $controller): array
    {
        if (!\is_string($controller)) {
            return [];
        }

        if (str_contains($controller, '::')) {
            [$class, $method] = explode('::', $controller, 2);
        } elseif (class_exists($controller)) {
            $class = $controller;
            $method = '__invoke';
        } else {
            return [];
        }

        if (!class_exists($class)) {
            return [];
        }

        try {
            $reflection = new \ReflectionMethod($class, $method);
        } catch (\ReflectionException) {
            return [];
        }

        $attributes = $reflection->getAttributes(ApiFilter::class);
        $apiFilters = [];
        foreach ($attributes as $attribute) {
            /** @var ApiFilter $apiFilter */
            $apiFilter = $attribute->newInstance();
            $apiFilters[$apiFilter->name] = $apiFilter;
        }

        return $apiFilters;
    }

    private function validateType(string $fieldName, string $typeName, ApiFilter $apiFilter): void
    {
        if ([] === $apiFilter->allowedTypes) {
            return;
        }

        if (!\in_array($typeName, $apiFilter->allowedTypes, true)) {
            throw new InvalidFilterException(\sprintf('Filter type "%s" is not allowed for "%s". Allowed types: %s.', $typeName, $fieldName, implode(', ', $apiFilter->allowedTypes)));
        }
    }

    private function resolveValue(string $fieldName, mixed $value, ApiFilter $apiFilter): mixed
    {
        if (null === $apiFilter->enumClass) {
            return $value;
        }

        if (\is_array($value)) {
            return array_map(fn (mixed $v): string|int => $this->castEnum($fieldName, $v, $apiFilter->enumClass), array_values($value));
        }

        return $this->castEnum($fieldName, $value, $apiFilter->enumClass);
    }

    /**
     * @param class-string<\BackedEnum> $enumClass
     */
    private function castEnum(string $fieldName, mixed $value, string $enumClass): string|int
    {
        if (!\is_string($value) && !\is_int($value)) {
            throw new InvalidFilterException(\sprintf('Invalid enum value for filter "%s".', $fieldName));
        }

        $backingType = (new \ReflectionEnum($enumClass))->getBackingType();
        $castedValue = 'int' === $backingType?->getName() ? (int) $value : (string) $value;
        $case = $enumClass::tryFrom($castedValue);

        if (null === $case) {
            throw new InvalidFilterException(\sprintf('Invalid enum value "%s" for filter "%s".', $value, $fieldName));
        }

        return $case->value;
    }
}
