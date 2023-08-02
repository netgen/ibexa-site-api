<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType\Content\Relations;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\MatchNone;
use Netgen\IbexaSiteApi\API\Settings;
use Netgen\IbexaSiteApi\API\Values\Content as SiteContent;
use Netgen\IbexaSiteApi\Core\Site\Plugins\FieldType\RelationResolver\Registry as RelationResolverRegistry;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Content;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_merge;

/**
 * QueryType for finding relations from specific relation fields of a Content.
 */
final class ForwardFields extends Content
{
    public function __construct(
        Settings $settings,
        private readonly RelationResolverRegistry $relationResolverRegistry,
    ) {
        parent::__construct($settings);
    }

    public static function getName(): string
    {
        return 'SiteAPI:Content/Relations/ForwardFields';
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
        /** @var \Netgen\IbexaSiteApi\API\Values\Content $content */
        $content = $parameters['content'];
        $fields = (array) $parameters['relation_field'];
        $idsGrouped = [[]];

        foreach ($fields as $identifier) {
            $field = $content->getField($identifier);
            $relationResolver = $this->relationResolverRegistry->get($field->fieldTypeIdentifier);
            $idsGrouped[] = $relationResolver->getRelationIds($field);
        }

        /** @var int[] $relatedContentIds */
        $relatedContentIds = array_merge(...$idsGrouped);

        if (empty($relatedContentIds)) {
            return new MatchNone();
        }

        return new ContentId($relatedContentIds);
    }
}
