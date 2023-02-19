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
    /** @var string[] */
    private array $prioritizedLanguages;
    private bool $useAlwaysAvailable;
    private int $rootLocationId;
    private bool $showHiddenItems;
    private bool $failOnMissingField;

    /**
     * @param string[] $prioritizedLanguages
     */
    public function __construct(
        array $prioritizedLanguages,
        bool $useAlwaysAvailable,
        int $rootLocationId,
        bool $showHiddenItems,
        bool $failOnMissingField,
    ) {
        $this->prioritizedLanguages = $prioritizedLanguages;
        $this->useAlwaysAvailable = $useAlwaysAvailable;
        $this->rootLocationId = $rootLocationId;
        $this->showHiddenItems = $showHiddenItems;
        $this->failOnMissingField = $failOnMissingField;
    }

    /**
     * @return bool|int|string[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException
     */
    public function __get(string $property): bool|int|array
    {
        switch ($property) {
            case 'prioritizedLanguages':
                return $this->prioritizedLanguages;

            case 'useAlwaysAvailable':
                return $this->useAlwaysAvailable;

            case 'rootLocationId':
                return $this->rootLocationId;

            case 'showHiddenItems':
                return $this->showHiddenItems;

            case 'failOnMissingField':
                return $this->failOnMissingField;
        }

        throw new PropertyNotFoundException($property, __CLASS__);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyReadOnlyException
     */
    public function __set(string $property, mixed $value): void
    {
        throw new PropertyReadOnlyException($property, __CLASS__);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException
     */
    public function __isset(string $property): bool
    {
        switch ($property) {
            case 'prioritizedLanguages':
            case 'useAlwaysAvailable':
            case 'rootLocationId':
            case 'showHiddenItems':
            case 'failOnMissingField':
                return true;
        }

        throw new PropertyNotFoundException($property, __CLASS__);
    }
}
