<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType\Location\Relations;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalNot;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\MatchNone;
use InvalidArgumentException;
use Netgen\IbexaSiteApi\API\Values\Content as SiteContent;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Location;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function array_merge;
use function sprintf;

/**
 * QueryType for finding specific Tag fields relations in a given Content.
 *
 * Note: only visible main Locations of the related Content will be used.
 */
final class TagFields extends Location
{
    public static function getName(): string
    {
        return 'SiteAPI:Location/Relations/TagFields';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'content',
            'relation_field',
        ]);

        $resolver->setAllowedTypes('content', SiteContent::class);
        $resolver->setAllowedTypes('relation_field', ['string', 'string[]']);

        $resolver->setDefined('exclude_self');
        $resolver->setAllowedTypes('exclude_self', ['null', 'bool']);

        $resolver->setDefaults([
            'exclude_self' => true,
            'main' => true,
            'visible' => true,
        ]);
    }

    protected function getFilterCriteria(array $parameters): Criterion|array|null
    {
        /** @var \Netgen\IbexaSiteApi\API\Values\Content $content */
        $content = $parameters['content'];

        /** @var string[] $fields */
        $fields = (array) $parameters['relation_field'];

        $tagIds = $this->extractTagIds($content, $fields);

        if (empty($tagIds)) {
            return new MatchNone();
        }

        $criteria = [
            new TagId($tagIds),
        ];

        if ($parameters['exclude_self']) {
            $criteria[] = new LogicalNot(new ContentId($content->id));
        }

        return $criteria;
    }

    /**
     * Extract Tag IDs from $fields in the given $content.
     *
     * @param string[] $fields
     */
    private function extractTagIds(SiteContent $content, array $fields): array
    {
        $tagsIdsGrouped = [[]];

        foreach ($fields as $identifier) {
            $field = $content->getField($identifier);

            if ($field->isSurrogate()) {
                continue;
            }

            $fieldType = $field->fieldTypeIdentifier;

            if ($fieldType !== 'eztags') {
                throw new InvalidArgumentException(
                    sprintf(
                        "Field '%s' is expected to be of 'eztags' type, '%s' found",
                        $identifier,
                        $fieldType,
                    ),
                );
            }

            /** @var \Netgen\TagsBundle\Core\FieldType\Tags\Value $value */
            $value = $field->value;
            $tagsIdsGrouped[] = array_map(static fn (Tag $tag) => $tag->id, $value->tags);
        }

        return array_merge(...$tagsIdsGrouped);
    }
}
