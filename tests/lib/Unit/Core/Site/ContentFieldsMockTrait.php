<?php

declare(strict_types=1);

namespace Netgen\IbexaSiteApi\Tests\Unit\Core\Site;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo as RepoContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Ibexa\Core\Repository\Repository as CoreRepository;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Content as RepoContent;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Netgen\IbexaSiteApi\API\Routing\UrlGenerator;
use Netgen\IbexaSiteApi\API\Site;
use Netgen\IbexaSiteApi\Core\Site\DomainObjectMapper;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

/**
 * Used for mocking Site API Content with Fields.
 */
trait ContentFieldsMockTrait
{
    protected null|MockObject|Site $siteMock = null;

    /** @var \Netgen\IbexaSiteApi\Core\Site\DomainObjectMapper[] */
    protected array $domainObjectMapper = [];

    /** @var \Netgen\IbexaSiteApi\Core\Site\DomainObjectMapper[] */
    protected array $domainObjectMapperForContentWithoutFields = [];
    protected null|CoreRepository|MockObject $repositoryMock = null;
    protected null|MockObject|UrlGenerator $urlGeneratorMock = null;
    protected null|CoreRepository|MockObject $repositoryMockForContentWithoutFields = null;
    protected ?VersionInfo $repoVersionInfo = null;
    protected ?RepoContent $repoContent = null;
    protected ?RepoContent $repoContentWithoutFields = null;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Field[] */
    protected ?array $internalFields = null;
    protected null|FieldDefinitionCollection $fieldDefinitions = null;
    protected null|ContentTypeService|MockObject $contentTypeServiceMock = null;
    protected null|FieldTypeService|MockObject $fieldTypeServiceMock = null;
    protected null|FieldType|MockObject $fieldTypeMock = null;

    /**
     * @see \PHPUnit\Framework\TestCase
     */
    abstract public function getMockBuilder(string $className): MockBuilder;

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Field[]
     */
    abstract public function internalGetRepoFields(): array;

    protected function getSiteMock(): MockObject|Site
    {
        if ($this->siteMock !== null) {
            return $this->siteMock;
        }

        $this->siteMock = $this->getMockBuilder(Site::class)->getMock();

        return $this->siteMock;
    }

    protected function getDomainObjectMapper(bool $failOnMissingField = true): DomainObjectMapper
    {
        if (isset($this->domainObjectMapper[$failOnMissingField])) {
            return $this->domainObjectMapper[$failOnMissingField];
        }

        $this->domainObjectMapper[$failOnMissingField] = new DomainObjectMapper(
            $this->getSiteMock(),
            $this->getRepositoryMock(),
            $this->getUrlGeneratorMock(),
            $failOnMissingField,
            new NullLogger(),
        );

        return $this->domainObjectMapper[$failOnMissingField];
    }

    protected function getDomainObjectMapperForContentWithoutFields(bool $failOnMissingField = true): DomainObjectMapper
    {
        if (isset($this->domainObjectMapperForContentWithoutFields[$failOnMissingField])) {
            return $this->domainObjectMapperForContentWithoutFields[$failOnMissingField];
        }

        $this->domainObjectMapperForContentWithoutFields[$failOnMissingField] = new DomainObjectMapper(
            $this->getSiteMock(),
            $this->getRepositoryMockForContentWithoutFields(),
            $this->getUrlGeneratorMock(),
            $failOnMissingField,
            new NullLogger(),
        );

        return $this->domainObjectMapperForContentWithoutFields[$failOnMissingField];
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

        $repoContent = $this->getRepoContent();
        $this->repositoryMock->method('sudo')->willReturn($repoContent);

        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $this->repositoryMock->method('getContentTypeService')->willReturn($contentTypeServiceMock);

        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $this->repositoryMock->method('getFieldTypeService')->willReturn($fieldTypeServiceMock);

        return $this->repositoryMock;
    }

    protected function getUrlGeneratorMock(): MockObject|UrlGenerator
    {
        if ($this->urlGeneratorMock !== null) {
            return $this->urlGeneratorMock;
        }

        $this->urlGeneratorMock = $this
            ->getMockBuilder(UrlGenerator::class)
            ->getMock();

        return $this->urlGeneratorMock;
    }

    protected function getRepositoryMockForContentWithoutFields(): CoreRepository|MockObject
    {
        if ($this->repositoryMockForContentWithoutFields !== null) {
            return $this->repositoryMockForContentWithoutFields;
        }

        $this->repositoryMockForContentWithoutFields = $this
            ->getMockBuilder(CoreRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repoContent = $this->getRepoContentWithoutFields();
        $this->repositoryMockForContentWithoutFields->method('sudo')->willReturn($repoContent);

        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $this->repositoryMockForContentWithoutFields->method('getContentTypeService')->willReturn($contentTypeServiceMock);

        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $this->repositoryMockForContentWithoutFields->method('getFieldTypeService')->willReturn($fieldTypeServiceMock);

        return $this->repositoryMockForContentWithoutFields;
    }

    protected function getFieldTypeServiceMock(): FieldTypeService|MockObject
    {
        if ($this->fieldTypeServiceMock !== null) {
            return $this->fieldTypeServiceMock;
        }

        $this->fieldTypeServiceMock = $this
            ->getMockBuilder(FieldTypeService::class)
            ->getMock();

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->fieldTypeServiceMock
            ->method('getFieldType')
            ->willReturn($fieldTypeMock);

        return $this->fieldTypeServiceMock;
    }

    protected function getFieldTypeMock(): FieldType|MockObject
    {
        if ($this->fieldTypeMock !== null) {
            return $this->fieldTypeMock;
        }

        $this->fieldTypeMock = $this
            ->getMockBuilder(FieldType::class)
            ->getMock();

        $this->fieldTypeMock
            ->method('isEmptyValue')
            ->willReturnCallback(static fn ($field) => empty($field->value));

        return $this->fieldTypeMock;
    }

    protected function getContentTypeServiceMock(): ContentTypeService|MockObject
    {
        if ($this->contentTypeServiceMock !== null) {
            return $this->contentTypeServiceMock;
        }

        $this->contentTypeServiceMock = $this->getMockBuilder(ContentTypeService::class)->getMock();

        $this->contentTypeServiceMock
            ->method('loadContentType')
            ->with(42)
            ->willReturn(
                new ContentType([
                    'id' => 42,
                    'identifier' => 'test',
                    'fieldDefinitions' => $this->getRepoFieldDefinitions(),
                ]),
            );

        return $this->contentTypeServiceMock;
    }

    protected function getRepoFieldDefinitions(): FieldDefinitionCollection
    {
        if ($this->fieldDefinitions !== null) {
            return $this->fieldDefinitions;
        }

        $this->fieldDefinitions = $this->internalGetRepoFieldDefinitions();

        return $this->fieldDefinitions;
    }

    abstract protected function internalGetRepoFieldDefinitions(): FieldDefinitionCollection;

    protected function getRepoContent(): Content
    {
        if ($this->repoContent !== null) {
            return $this->repoContent;
        }

        $repoVersionInfo = $this->getRepoVersionInfo();

        $this->repoContent = new RepoContent([
            'versionInfo' => $repoVersionInfo,
            'internalFields' => $this->getRepoFields(),
        ]);

        return $this->repoContent;
    }

    protected function getRepoContentWithoutFields(): Content
    {
        if ($this->repoContentWithoutFields !== null) {
            return $this->repoContentWithoutFields;
        }

        $repoVersionInfo = $this->getRepoVersionInfo();

        $this->repoContentWithoutFields = new RepoContent([
            'versionInfo' => $repoVersionInfo,
            'internalFields' => [],
        ]);

        return $this->repoContentWithoutFields;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Field[]
     */
    protected function getRepoFields(): array
    {
        if ($this->internalFields !== null) {
            return $this->internalFields;
        }

        $this->internalFields = $this->internalGetRepoFields();

        return $this->internalFields;
    }

    protected function getRepoVersionInfo(): VersionInfo
    {
        if ($this->repoVersionInfo !== null) {
            return $this->repoVersionInfo;
        }

        $repoContentInfo = $this->getRepoContentInfo();

        $this->repoVersionInfo = new VersionInfo([
            'contentInfo' => $repoContentInfo,
        ]);

        return $this->repoVersionInfo;
    }

    protected function getRepoContentInfo(): RepoContentInfo
    {
        return new RepoContentInfo([
            'id' => 1,
            'ownerId' => 24,
            'contentTypeId' => 42,
            'mainLanguageCode' => 'eng-GB',
        ]);
    }
}
