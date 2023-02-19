<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site;

use Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException;
use Netgen\IbexaSiteApi\API\Settings as BaseSettings;

/**
 * @internal
 *
 * Hint against API abstract class instead of this service:
 *
 * @see \Netgen\IbexaSiteApi\API\Settings
 */
final class Settings extends BaseSettings
{
    /**
     * @param string[] $prioritizedLanguages
     *
     * @noinspection PhpPropertyCanBeReadonlyInspection
     */
    public function __construct(
        private array $prioritizedLanguages,
        private readonly bool $useAlwaysAvailable,
        private readonly int $rootLocationId,
        private readonly bool $showHiddenItems,
        private readonly bool $failOnMissingField,
    ) {
    }

    /**
     * @return bool|int|string[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException
     */
    public function __get(string $property): bool|int|array
    {
        return match ($property) {
            'prioritizedLanguages' => $this->prioritizedLanguages,
            'useAlwaysAvailable' => $this->useAlwaysAvailable,
            'rootLocationId' => $this->rootLocationId,
            'showHiddenItems' => $this->showHiddenItems,
            'failOnMissingField' => $this->failOnMissingField,
            default => throw new PropertyNotFoundException($property, self::class),
        };
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException
     */
    public function __set(string $property, mixed $value): void
    {
        throw new PropertyReadOnlyException($property, self::class);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException
     */
    public function __isset(string $property): bool
    {
        return match ($property) {
            'prioritizedLanguages',
            'useAlwaysAvailable',
            'rootLocationId',
            'showHiddenItems',
            'failOnMissingField' => true,
            default => throw new PropertyNotFoundException($property, self::class),
        };
    }
}
