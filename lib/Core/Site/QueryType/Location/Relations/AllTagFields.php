<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Core\Site\QueryType\Location\Relations;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalNot;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\MatchNone;
use Netgen\IbexaSiteApi\API\Values\Content as SiteContent;
use Netgen\IbexaSiteApi\Core\Site\QueryType\Location;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag as TagValue;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function array_map;
use function array_merge;

/**
 * QueryType for finding all Tag relations in a given Content.
 *
 * Note: only visible main Locations of the related Content will be used.
 */
final class AllTagFields extends Location
{
    public static function getName(): string
    {
        return 'SiteAPI:Location/Relations/AllTagFields';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('content');
        $resolver->setAllowedTypes('content', SiteContent::class);

        $resolver->setDefined('exclude_self');
        $resolver->setAllowedTypes('exclude_self', ['null', 'bool']);

        $resolver->setDefaults([
            'exclude_self' => true,
            'main' => true,
            'visible' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function getFilterCriteria(array $parameters)
    {
        /** @var \Netgen\IbexaSiteApi\API\Values\Content $content */
        $content = $parameters['content'];
        $tagIds = $this->extractTagIds($content);

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
     * Extract all Tag IDs from the given $content.
     *
     * @param \Netgen\IbexaSiteApi\API\Values\Content $content
     *
     * @return int[]
     */
    private function extractTagIds(SiteContent $content): array
    {
        $tagsIdsGrouped = [[]];

        foreach ($content->fields as $field) {
            if ($field->fieldTypeIdentifier !== 'eztags') {
                continue;
            }

            /** @var \Netgen\TagsBundle\Core\FieldType\Tags\Value $value */
            $value = $field->value;
            $tagsIdsGrouped[] = array_map(static fn (TagValue $tag) => $tag->id, $value->tags);
        }

        return array_merge(...$tagsIdsGrouped);
    }
}
