<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType\Content\Relations;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\FieldRelation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\MatchNone;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Netgen\IbexaSiteApi\API\Values\Content as SiteContent;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Content;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * QueryType for finding reverse relations from specific fields towards a Content.
 */
final class ReverseFields extends Content
{
    public static function getName(): string
    {
        return 'SiteAPI:Content/Relations/ReverseFields';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'content',
            'relation_field',
        ]);

        $resolver->setAllowedTypes('content', SiteContent::class);
        $resolver->setAllowedTypes('relation_field', ['string', 'string[]']);
    }

    protected function getFilterCriteria(array $parameters): Criterion|array|null
    {
        $fields = (array) $parameters['relation_field'];

        if (empty($fields)) {
            return new MatchNone();
        }

        /** @var \Netgen\IbexaSiteApi\API\Values\Content $content */
        $content = $parameters['content'];
        $criteria = [];

        foreach ($fields as $identifier) {
            $criteria[] = new FieldRelation($identifier, Operator::CONTAINS, [$content->id]);
        }

        return $criteria;
    }
}
