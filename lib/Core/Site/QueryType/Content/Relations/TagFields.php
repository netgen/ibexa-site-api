<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType\Content\Relations;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalNot;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\MatchNone;
use InvalidArgumentException;
use Netgen\IbexaSiteApi\API\Values\Content as SiteContent;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Content;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function array_map;
use function array_merge;

/**
 * QueryType for finding specific Tag fields relations in a given Content.
 */
final class TagFields extends Content
{
    public static function getName(): string
    {
        return 'SiteAPI:Content/Relations/TagFields';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
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
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    protected function getFilterCriteria(array $parameters)
    {
        /** @var \Netgen\IbexaSiteApi\API\Values\Content $content */
        $content = $parameters['content'];

        /** @var string[] $fields */
        $fields = (array) $parameters['relation_field'];

        $tagIds = $this->extractTagIds($content, $fields);

        if (empty($tagIds)) {
            return new MatchNone();
        }

        $criteria = [];
        $criteria[] = new TagId($tagIds);

        if ($parameters['exclude_self']) {
            $criteria[] = new LogicalNot(new ContentId($content->id));
        }

        return $criteria;
    }

    /**
     * Extract Tag IDs from $fields in the given $content.
     *
     * @param string[] $fields
     *
     * @throws \InvalidArgumentException
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
                    "Field '{$identifier}' is expected to be of 'eztags' type, '{$fieldType}' found",
                );
            }

            /** @var \Netgen\TagsBundle\Core\FieldType\Tags\Value $value */
            $value = $field->value;
            $tagsIdsGrouped[] = array_map(static fn (Tag $tag) => $tag->id, $value->tags);
        }

        return array_merge(...$tagsIdsGrouped);
    }
}
