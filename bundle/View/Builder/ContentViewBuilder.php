<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Builder;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as APIContent;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Core\MVC\Symfony\View\Builder\ViewBuilder;
use Ibexa\Core\MVC\Symfony\View\Configurator;
use Ibexa\Core\MVC\Symfony\View\EmbedView;
use Ibexa\Core\MVC\Symfony\View\ParametersInjector;
use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;
use Netgen\Bundle\IbexaSiteApiBundle\View\LocationResolver;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\API\Values\Content;
use Netgen\IbexaSiteApi\API\Values\Location;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function in_array;
use function is_string;
use function mb_strpos;

/**
 * Builds ContentView objects.
 */
class ContentViewBuilder implements ViewBuilder
{
    private Site $site;
    private Repository $repository;
    private Configurator $viewConfigurator;
    private ParametersInjector $viewParametersInjector;
    private LocationResolver $locationResolver;

    public function __construct(
        Site $site,
        Repository $repository,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector,
        LocationResolver $locationResolver
    ) {
        $this->site = $site;
        $this->repository = $repository;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
        $this->locationResolver = $locationResolver;
    }

    public function matches($argument): bool
    {
        return is_string($argument) && mb_strpos($argument, 'ng_content:') !== false;
    }

    /**
     * @throws \Exception
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function buildView(array $parameters): ContentView
    {
        $view = new ContentView(null, [], $parameters['viewType']);
        $view->setIsEmbed($this->isEmbed($parameters));

        if ($parameters['viewType'] === null && $view->isEmbed()) {
            $view->setViewType(EmbedView::DEFAULT_VIEW_TYPE);
        }

        if (isset($parameters['locationId'])) {
            $location = $this->loadLocation($parameters['locationId']);
        } elseif (isset($parameters['location'])) {
            $location = $parameters['location'];
            if (!$location instanceof Location && $location instanceof APILocation) {
                $location = $this->loadLocation($location->id, false);
            }
        } else {
            $location = null;
        }

        if (isset($parameters['content'])) {
            $content = $parameters['content'];
            if (!$content instanceof Content && $content instanceof APIContent) {
                $content = $this->loadContent($content->contentInfo->id);
            }
        } else {
            if (isset($parameters['contentId'])) {
                $contentId = $parameters['contentId'];
            } elseif (isset($location)) {
                $contentId = $location->contentInfo->id;
            } else {
                throw new InvalidArgumentException(
                    'Content',
                    'No content could be loaded from parameters',
                );
            }

            $content = $view->isEmbed() ?
                $this->loadEmbeddedContent($contentId, $location) :
                $this->loadContent($contentId);
        }

        $view->setSiteContent($content);

        if ($location === null) {
            try {
                $location = $this->locationResolver->getLocation($content);
            } catch (NotFoundException $e) {
                // do nothing
            }
        }

        if (isset($location)) {
            if ($location->contentInfo->id !== $content->id) {
                throw new InvalidArgumentException(
                    'Location',
                    'Provided location does not belong to selected content',
                );
            }

            $view->setSiteLocation($location);
        }

        $this->viewParametersInjector->injectViewParameters($view, $parameters);
        $this->viewConfigurator->configure($view);

        return $view;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Netgen\IbexaSiteApi\API\Exceptions\TranslationNotMatchedException
     */
    private function loadContent(int $contentId): Content
    {
        return $this->site->getLoadService()->loadContent($contentId);
    }

    /**
     * Loads the embedded content with id $contentId.
     * Will load the content with sudo(), and check if the user can view_embed this content, for the given location
     * if provided.
     *
     * @throws \Exception
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function loadEmbeddedContent(int $contentId, ?Location $location = null): Content
    {
        /** @var \Netgen\IbexaSiteApi\API\Values\Content $content */
        $content = $this->repository->sudo(
            fn (): Content => $this->site->getLoadService()->loadContent($contentId),
        );

        $versionInfo = $content->versionInfo;

        if (!$this->canReadOrViewEmbed($versionInfo->contentInfo, $location)) {
            throw new UnauthorizedException(
                'content',
                'read|view_embed',
                ['contentId' => $contentId, 'locationId' => $location !== null ? $location->id : 'n/a'],
            );
        }

        // Check that Content is published, since sudo allows loading unpublished content.
        if (
            $versionInfo->status !== VersionInfo::STATUS_PUBLISHED
            && !$this->repository->getPermissionResolver()->canUser('content', 'versionread', $versionInfo)
        ) {
            throw new UnauthorizedException('content', 'versionread', ['contentId' => $contentId]);
        }

        return $content;
    }

    /**
     * Loads a Location with visibility check.
     *
     * @todo Do we need to handle permissions here ?
     *
     * @throws \Exception
     */
    private function loadLocation(int $locationId, bool $checkVisibility = true): Location
    {
        $location = $this->repository->sudo(
            fn (Repository $repository): Location => $this->site->getLoadService()->loadLocation($locationId),
        );

        if ($checkVisibility && $location->innerLocation->invisible) {
            throw new NotFoundHttpException(
                'Location cannot be displayed as it is flagged as invisible.',
            );
        }

        return $location;
    }

    /**
     * Checks if a user can read a content, or view it as an embed.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function canReadOrViewEmbed(ContentInfo $contentInfo, ?Location $location = null): bool
    {
        $targets = isset($location) ? [$location->innerLocation] : [];

        return
            $this->repository->getPermissionResolver()->canUser('content', 'read', $contentInfo, $targets)
            || $this->repository->getPermissionResolver()->canUser('content', 'view_embed', $contentInfo, $targets);
    }

    /**
     * Checks if the view is an embed one.
     * Uses either the controller action (embedAction), or the viewType (embed/embed-inline).
     *
     * @param array $parameters The ViewBuilder parameters array
     */
    private function isEmbed(array $parameters): bool
    {
        if ($parameters['_controller'] === 'ng_content:embedAction') {
            return true;
        }

        if (in_array($parameters['viewType'], ['embed', 'embed-inline'], true)) {
            return true;
        }

        return false;
    }
}
