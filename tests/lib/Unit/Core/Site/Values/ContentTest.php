<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site\Values;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo as RepoContentInfo;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository as CoreRepository;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Netgen\IbexaSiteApi\API\LoadService;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\API\Values\Content as APIContent;
use Netgen\IbexaSiteApi\Core\Site\DomainObjectMapper;
use Netgen\IbexaSiteApi\Core\Site\Values\Content;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Content value unit tests.
 *
 * @see \Netgen\IbexaSiteApi\API\Values\Content
 *
 * @internal
 */
final class ContentTest extends TestCase
{
    protected Site|MockObject|null $siteMock = null;
    protected ?DomainObjectMapper $domainObjectMapper = null;
    protected MockObject|ContentService|null $contentServiceMock = null;
    protected MockObject|ContentTypeService|null $contentTypeServiceMock = null;
    protected FieldTypeService|MockObject|null $fieldTypeServiceMock = null;
    protected LoadService|MockObject|null $loadServiceMock = null;
    protected UserService|MockObject|null $userServiceMock = null;
    protected CoreRepository|MockObject|null $repositoryMock = null;

    protected function setUp(): void
    {
        $this->getSiteMock();
        $this->getDomainObjectMapper();
        $this->getLoadServiceMock();
        $this->getUserServiceMock();
        $this->getRepositoryMock();

        parent::setUp();
    }

    public function testContentOwnerExists(): void
    {
        $content = $this->getMockedContent();
        $ownerMock = $this->getMockBuilder(APIContent::class)->getMock();

        $this->repositoryMock->expects(self::once())
            ->method('sudo')
            ->willReturn($ownerMock);

        self::assertSame($ownerMock, $content->owner);
    }

    public function testContentOwnerExistsRepeated(): void
    {
        $content = $this->getMockedContent();
        $ownerMock = $this->getMockBuilder(APIContent::class)->getMock();

        $this->repositoryMock->expects(self::once())
            ->method('sudo')
            ->willReturn($ownerMock);

        self::assertSame($ownerMock, $content->owner);
        self::assertSame($ownerMock, $content->owner);
    }

    public function testContentOwnerDoesNotExist(): void
    {
        $content = $this->getMockedContent();

        $this->repositoryMock->expects(self::once())
            ->method('sudo')
            ->willReturn(null);

        self::assertNull($content->owner);
    }

    public function testContentOwnerDoesNotExistRepeated(): void
    {
        $content = $this->getMockedContent();

        $this->repositoryMock->expects(self::once())
            ->method('sudo')
            ->willReturn(null);

        self::assertNull($content->owner);
        self::assertNull($content->owner);
    }

    public function testContentInnerOwnerUserExists(): void
    {
        $content = $this->getMockedContent();
        $ownerUserMock = $this->getMockBuilder(User::class)->getMock();

        $this
            ->userServiceMock
            ->expects(self::once())
            ->method('loadUser')
            ->with(14)
            ->willReturn($ownerUserMock);

        self::assertSame($ownerUserMock, $content->innerOwnerUser);
    }

    public function testContentInnerOwnerUserExistsRepeated(): void
    {
        $content = $this->getMockedContent();
        $ownerUserMock = $this->getMockBuilder(User::class)->getMock();

        $this
            ->userServiceMock
            ->expects(self::once())
            ->method('loadUser')
            ->with(14)
            ->willReturn($ownerUserMock);

        self::assertSame($ownerUserMock, $content->innerOwnerUser);
        self::assertSame($ownerUserMock, $content->innerOwnerUser);
    }

    public function testContentInnerOwnerUserDoesNotExist(): void
    {
        $content = $this->getMockedContent();

        $this
            ->userServiceMock
            ->expects(self::once())
            ->method('loadUser')
            ->with(14)
            ->willThrowException(
                new NotFoundException('User', 14),
            );

        self::assertNull($content->innerOwnerUser);
    }

    public function testContentInnerOwnerUserDoesNotExistRepeated(): void
    {
        $content = $this->getMockedContent();

        $this
            ->userServiceMock
            ->expects(self::once())
            ->method('loadUser')
            ->with(14)
            ->willThrowException(
                new NotFoundException('User', 14),
            );

        self::assertNull($content->innerOwnerUser);
        self::assertNull($content->innerOwnerUser);
    }

    protected function getMockedContent(): APIContent
    {
        return new Content(
            [
                'id' => 42,
                'site' => $this->getSiteMock(),
                'name' => 'Krešo',
                'mainLocationId' => 123,
                'domainObjectMapper' => $this->getDomainObjectMapper(),
                'repository' => $this->getRepositoryMock(),
                'innerVersionInfo' => new VersionInfo([
                    'contentInfo' => new RepoContentInfo([
                        'ownerId' => 14,
                        'contentTypeId' => 42,
                    ]),
                ]),
                'languageCode' => 'eng-GB',
            ],
            true,
            new NullLogger(),
        );
    }

    protected function getSiteMock(): MockObject|Site
    {
        if ($this->siteMock !== null) {
            return $this->siteMock;
        }

        $this->siteMock = $this
            ->getMockBuilder(Site::class)
            ->getMock();

        $this->siteMock
            ->method('getLoadService')
            ->willReturn($this->getLoadServiceMock());

        return $this->siteMock;
    }

    protected function getDomainObjectMapper(): DomainObjectMapper
    {
        if ($this->domainObjectMapper !== null) {
            return $this->domainObjectMapper;
        }

        $this->domainObjectMapper = new DomainObjectMapper(
            $this->getSiteMock(),
            $this->getRepositoryMock(),
            true,
            new NullLogger(),
        );

        return $this->domainObjectMapper;
    }

    protected function getLoadServiceMock(): LoadService|MockObject
    {
        if ($this->loadServiceMock !== null) {
            return $this->loadServiceMock;
        }

        $this->loadServiceMock = $this
            ->getMockBuilder(LoadService::class)
            ->getMock();

        return $this->loadServiceMock;
    }

    protected function getContentServiceMock(): ContentService|MockObject
    {
        if ($this->contentServiceMock !== null) {
            return $this->contentServiceMock;
        }

        $this->contentServiceMock = $this
            ->getMockBuilder(ContentService::class)
            ->getMock();

        return $this->contentServiceMock;
    }

    protected function getContentTypeServiceMock(): ContentTypeService|MockObject
    {
        if ($this->contentTypeServiceMock !== null) {
            return $this->contentTypeServiceMock;
        }

        $this->contentTypeServiceMock = $this
            ->getMockBuilder(ContentTypeService::class)
            ->getMock();

        $this->contentTypeServiceMock
            ->method('loadContentType')
            ->with(42)
            ->willReturn(new ContentType([
                'identifier' => 'content_type_identifier',
                'fieldDefinitions' => new FieldDefinitionCollection(),
            ]));

        return $this->contentTypeServiceMock;
    }

    protected function getFieldTypeServiceMock(): FieldTypeService|MockObject
    {
        if ($this->fieldTypeServiceMock !== null) {
            return $this->fieldTypeServiceMock;
        }

        $this->fieldTypeServiceMock = $this
            ->getMockBuilder(FieldTypeService::class)
            ->getMock();

        return $this->fieldTypeServiceMock;
    }

    protected function getUserServiceMock(): MockObject|UserService
    {
        if ($this->userServiceMock !== null) {
            return $this->userServiceMock;
        }

        $this->userServiceMock = $this
            ->getMockBuilder(UserService::class)
            ->getMock();

        return $this->userServiceMock;
    }

    protected function getRepositoryMock(): CoreRepository|MockObject
    {
        if ($this->repositoryMock !== null) {
            return $this->repositoryMock;
        }

        $this->repositoryMock = $this
            ->getMockBuilder(CoreRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock->method('getContentService')->willReturn($this->getContentServiceMock());
        $this->repositoryMock->method('getContentTypeService')->willReturn($this->getContentTypeServiceMock());
        $this->repositoryMock->method('getFieldTypeService')->willReturn($this->getFieldTypeServiceMock());
        $this->repositoryMock->method('getUserService')->willReturn($this->getUserServiceMock());

        return $this->repositoryMock;
    }
}
