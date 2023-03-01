<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType\Location;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Netgen\IbexaSiteApi\API\Settings;
use Netgen\IbexaSiteApi\API\Values\Location as SiteLocation;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Location;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function sprintf;

/**
 * Children Location QueryType.
 *
 * @see \Netgen\IbexaSiteApi\Core\Site\QueryType\Location
 */
final class Children extends Location
{
    public function __construct(
        Settings $settings,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
        parent::__construct($settings);
    }

    public static function getName(): string
    {
        return 'SiteAPI:Location/Children';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->remove(['depth', 'parent_location_id', 'subtree']);
        $resolver->setRequired('location');
        $resolver->setAllowedTypes('location', SiteLocation::class);

        $resolver->setDefault(
            'sort',
            function (Options $options): array {
                /** @var \Netgen\IbexaSiteApi\API\Values\Location $location */
                $location = $options['location'];

                try {
                    return $location->getSortClauses();
                } catch (NotImplementedException $exception) {
                    $this->logger->notice(
                        sprintf(
                            'Cannot use sort clauses from parent location: %s',
                            $exception->getMessage(),
                        ),
                    );

                    return [];
                }
            },
        );
    }

    protected function getFilterCriteria(array $parameters): Criterion
    {
        /** @var \Netgen\IbexaSiteApi\API\Values\Location $location */
        $location = $parameters['location'];

        return new ParentLocationId($location->id);
    }
}
