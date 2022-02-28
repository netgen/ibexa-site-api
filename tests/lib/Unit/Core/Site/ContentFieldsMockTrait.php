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
    /**
     * @var \Netgen\IbexaSiteApi\API\Site|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $siteMock;

    /**
     * @var \Netgen\IbexaSiteApi\Core\Site\DomainObjectMapper[]
     */
    protected $domainObjectMapper = [];

    /**
     * @var \Netgen\IbexaSiteApi\Core\Site\DomainObjectMapper[]
     */
    protected $domainObjectMapperForContentWithoutFields = [];

    /**
     * @var \Ibexa\Contracts\Core\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $repositoryMock;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $repositoryMockForContentWithoutFields;

    /**
     * @var \Ibexa\Core\Repository\Values\Content\VersionInfo
     */
    protected $repoVersionInfo;

    protected ?RepoContent $repoContent = null;
    protected ?RepoContent $repoContentWithoutFields = null;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Field[]
     */
    protected ?array $internalFields = null;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCollection
     */
    protected $fieldDefinitions;

    /**
     * @var \Ibexa\Contracts\Core\Repository\ContentTypeService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contentTypeServiceMock;

    /**
     * @var \Ibexa\Contracts\Core\Repository\FieldTypeService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fieldTypeServiceMock;

    /**
     * @var \Ibexa\Core\FieldType\FieldType|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fieldTypeMock;

    /**
     * @see \PHPUnit\Framework\TestCase
     */
    abstract public function getMockBuilder(string $className): MockBuilder;

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Field[]
     */
    abstract public function internalGetRepoFields(): array;

    /**
     * @return \Netgen\IbexaSiteApi\API\Site|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getSiteMock(): MockObject
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
            $failOnMissingField,
            new NullLogger(),
        );

        return $this->domainObjectMapperForContentWithoutFields[$failOnMissingField];
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRepositoryMock(): MockObject
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

    /**
     * @return \Ibexa\Contracts\Core\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRepositoryMockForContentWithoutFields(): MockObject
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

    /**
     * @return \Ibexa\Contracts\Core\Repository\FieldTypeService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFieldTypeServiceMock(): MockObject
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

    /**
     * @return \Ibexa\Contracts\Core\Repository\FieldType|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFieldTypeMock(): MockObject
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

    /**
     * @return \Ibexa\Contracts\Core\Repository\ContentTypeService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getContentTypeServiceMock(): MockObject
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

    /**
     * @return \Ibexa\Core\Repository\Values\Content\Content|\PHPUnit\Framework\MockObject\MockObject
     */
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

    /**
     * @return \Ibexa\Core\Repository\Values\Content\Content|\PHPUnit\Framework\MockObject\MockObject
     */
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
            'ownerId' => 'ownerId',
            'contentTypeId' => 42,
            'mainLanguageCode' => 'eng-GB',
        ]);
    }
}
