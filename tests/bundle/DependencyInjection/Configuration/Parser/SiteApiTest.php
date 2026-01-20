<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension;
use Ibexa\Tests\Bundle\Core\DependencyInjection\Configuration\Parser\AbstractParserTestCase;
use Netgen\Bundle\IbexaSiteApiBundle\DependencyInjection\Configuration\Parser\SiteApi;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

use function file_get_contents;
use function preg_quote;
use function sprintf;

#[Group('config')]
#[AllowMockObjectsWithoutExpectations]
final class SiteApiTest extends AbstractParserTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $loader = new YamlFileLoader(
            $this->container,
            new FileLocator(__DIR__ . '/../../Fixtures'),
        );

        $loader->load('parameters.yaml');
    }

    public function testDefaultConfiguration(): void
    {
        $this->load();

        $this->assertConfigResolverParameterValue('ng_site_api.site_api_is_primary_content_view', false, 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.fallback_to_secondary_content_view', true, 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.fallback_without_subrequest', true, 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.richtext_embed_without_subrequest', false, 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.use_always_available_fallback', true, 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.show_hidden_items', false, 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.fail_on_missing_field', '%kernel.debug%', 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.render_missing_field_info', false, 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.enable_internal_view_route', true, 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.redirect_internal_view_route_to_url_alias', true, 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.named_queries', [], 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.named_objects', [], 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.cross_siteaccess_content.enabled', true, 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.cross_siteaccess_content.external_subtree_roots', [], 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.cross_siteaccess_content.included_siteaccesses', [], 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.cross_siteaccess_content.included_siteaccess_groups', [], 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.cross_siteaccess_content.excluded_siteaccesses', [], 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.cross_siteaccess_content.excluded_siteaccess_groups', [], 'ibexa_demo_site');
        $this->assertConfigResolverParameterValue('ng_site_api.cross_siteaccess_content.prefer_main_language', true, 'ibexa_demo_site');
    }

    public static function getBooleanConfigurationNames(): array
    {
        return [
            'site_api_is_primary_content_view',
            'fallback_to_secondary_content_view',
            'fallback_without_subrequest',
            'richtext_embed_without_subrequest',
            'use_always_available_fallback',
            'fail_on_missing_field',
            'render_missing_field_info',
        ];
    }

    public static function getBooleanConfigurationValidValuePairs(): array
    {
        return [
            [
                true,
                true,
            ],
            [
                false,
                false,
            ],
        ];
    }

    public static function provideBooleanConfigurationValidCases(): iterable
    {
        $names = self::getBooleanConfigurationNames();
        $valuePairs = self::getBooleanConfigurationValidValuePairs();

        foreach ($names as $name) {
            foreach ($valuePairs as $valuePair) {
                yield [
                    $name,
                    $valuePair[0],
                    $valuePair[1],
                ];
            }
        }
    }

    #[DataProvider('provideBooleanConfigurationValidCases')]
    public function testBooleanConfigurationValid(string $name, mixed $config, mixed $expectedValue): void
    {
        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        $name => $config,
                    ],
                ],
            ],
        ]);

        $this->assertConfigResolverParameterValue(
            'ng_site_api.' . $name,
            $expectedValue,
            'ibexa_demo_site',
        );
    }

    public static function getBooleanConfigurationInvalidValues(): array
    {
        return [
            0,
            1,
            'true',
            'false',
            [],
        ];
    }

    public static function provideBooleanConfigurationInvalidCases(): iterable
    {
        $names = self::getBooleanConfigurationNames();
        $values = self::getBooleanConfigurationInvalidValues();

        foreach ($names as $name) {
            foreach ($values as $value) {
                yield [
                    $name,
                    $value,
                ];
            }
        }
    }

    #[DataProvider('provideBooleanConfigurationInvalidCases')]
    public function testBooleanConfigurationInvalid(string $name, mixed $config): void
    {
        $this->expectException(InvalidTypeException::class);

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        $name => $config,
                    ],
                ],
            ],
        ]);
    }

    public static function getNamedObjectConfigurationNames(): array
    {
        return [
            'content',
            'locations',
            'tags',
        ];
    }

    public static function getValidNamedObjectConfigurationValuePairs(): array
    {
        return [
            [
                'napolitanke' => 42,
            ],
            [
                'napolitanke' => 'qwe5678',
            ],
            [
                'sardine' => 12,
                'napolitanke' => 'asd1234',
            ],
        ];
    }

    public static function provideNamedObjectConfigurationValidCases(): iterable
    {
        $names = self::getNamedObjectConfigurationNames();
        $values = self::getValidNamedObjectConfigurationValuePairs();

        foreach ($names as $name) {
            foreach ($values as $value) {
                yield [
                    $name,
                    $value,
                ];
            }
        }
    }

    #[DataProvider('provideNamedObjectConfigurationValidCases')]
    public function testNamedObjectConfigurationValid(string $name, array $configuration): void
    {
        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'named_objects' => [
                            $name => $configuration,
                        ],
                    ],
                ],
            ],
        ]);

        $defaultValues = [
            'content' => [],
            'locations' => [],
            'tags' => [],
        ];

        $this->assertConfigResolverParameterValue(
            'ng_site_api.named_objects',
            [$name => $configuration] + $defaultValues,
            'ibexa_demo_site',
        );

        $this->assertContainerBuilderHasParameter(
            'ibexa.site_access.config.ibexa_demo_group.ng_site_api.named_objects',
            [$name => $configuration] + $defaultValues,
        );
    }

    public static function getNamedObjectInvalidConfigurations(): array
    {
        return [
            [
                'the-object' => 12,
            ],
            [
                'an object' => 12,
            ],
            [
                '123object' => 12,
            ],
            [
                'the:object' => 12,
            ],
            [
                'object?' => 12,
            ],
        ];
    }

    public static function provideNamedObjectConfigurationInvalidCases(): iterable
    {
        $names = self::getNamedObjectConfigurationNames();
        $configurations = self::getNamedObjectInvalidConfigurations();

        foreach ($names as $name) {
            foreach ($configurations as $configuration) {
                yield [
                    $name,
                    $configuration,
                ];
            }
        }
    }

    #[DataProvider('provideNamedObjectConfigurationInvalidCases')]
    public function testNamedObjectConfigurationInvalid(string $name, array $configuration): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'named_objects' => [
                            $name => $configuration,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public static function provideNamedObjectDefaultValuesCases(): iterable
    {
        $defaultValues = [
            'content' => [],
            'locations' => [],
            'tags' => [],
        ];

        return [
            [
                null,
                $defaultValues,
            ],
            [
                [],
                $defaultValues,
            ],
        ];
    }

    #[DataProvider('provideNamedObjectDefaultValuesCases')]
    public function testNamedObjectDefaultValues(mixed $configurationValues, array $expectedConfigurationValues): void
    {
        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'named_objects' => $configurationValues,
                    ],
                ],
            ],
        ]);

        $this->assertConfigResolverParameterValue(
            'ng_site_api.named_objects',
            $expectedConfigurationValues,
            'ibexa_demo_site',
        );
    }

    public static function provideNamedQueryConfigurationValidCases(): iterable
    {
        return [
            [
                [
                    'query_type' => 'query_type',
                ],
            ],
            [
                [
                    'query_type' => 'query_type_name',
                    'use_filter' => false,
                ],
            ],
            [
                [
                    'query_type' => 'query_type_name',
                    'max_per_page' => 10,
                ],
            ],
            [
                [
                    'query_type' => 'query_type_name',
                    'max_per_page' => 10,
                    'page' => 2,
                ],
            ],
            [
                [
                    'query_type' => 'query_type_name',
                    'max_per_page' => 10,
                    'page' => 2,
                    'parameters' => [
                        'some' => 'parameters',
                    ],
                    'use_filter' => true,
                ],
            ],
        ];
    }

    #[DataProvider('provideNamedQueryConfigurationValidCases')]
    public function testNamedQueryConfigurationValid(array $configurationValues): void
    {
        $queryName = 'query_name';

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'named_queries' => [
                            $queryName => $configurationValues,
                        ],
                    ],
                ],
            ],
        ]);

        $defaultValues = [
            'use_filter' => true,
            'max_per_page' => 25,
            'page' => 1,
            'parameters' => [],
        ];

        $this->assertConfigResolverParameterValue(
            'ng_site_api.named_queries',
            [$queryName => $configurationValues + $defaultValues],
            'ibexa_demo_site',
        );
        // Avoid detecting risky tests
        self::assertTrue(true);
    }

    public static function provideNamedQueryConfigurationInvalidCases(): iterable
    {
        return [
            [
                [
                    [
                        'query_type' => 'query_type',
                    ],
                ],
                'The attribute "key" must be set',
            ],
            [
                [
                    '123abc' => [
                        'query_type' => 'query_type',
                    ],
                ],
                'Query key must be a string conforming to a valid Twig variable name',
            ],
            [
                [
                    'some_key' => [
                        'page' => 2,
                    ],
                ],
                'The child config "query_type" under "ibexa.system.ibexa_demo_group.ng_site_api.named_queries.some_key" must be configured',
            ],
            [
                [
                    'some_key' => [
                        'query_type' => 'query_type_name',
                        'parameters' => 'parameters',
                    ],
                ],
                'Expected "array", but got "string"',
            ],
            [
                [
                    'query_name' => [
                        'query_type' => 'query_type_name',
                        'use_filter' => [],
                    ],
                ],
                'Expected "scalar", but got "array"',
            ],
            [
                [
                    'query_name' => [
                        'query_type' => 'query_type_name',
                        'page' => [],
                    ],
                ],
                'Expected "scalar", but got "array"',
            ],
        ];
    }

    #[DataProvider('provideNamedQueryConfigurationInvalidCases')]
    public function testNamedQueryConfigurationInvalid(array $configurationValues, string $message): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $message = preg_quote($message, '/');
        $this->expectExceptionMessageMatches(sprintf('/%s/', $message));

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'named_queries' => $configurationValues,
                    ],
                ],
            ],
        ]);
    }

    public static function provideNamedQueryConfigurationDefaultValuesCases(): iterable
    {
        return [
            [
                [
                    'some_key' => [
                        'query_type' => 'query_type',
                    ],
                ],
                [
                    'some_key' => [
                        'query_type' => 'query_type',
                        'use_filter' => true,
                        'max_per_page' => 25,
                        'page' => 1,
                        'parameters' => [],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('provideNamedQueryConfigurationDefaultValuesCases')]
    public function testNamedQueryConfigurationDefaultValues(array $configurationValues, array $expectedConfigurationValues): void
    {
        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'named_queries' => $configurationValues,
                    ],
                ],
            ],
        ]);

        $this->assertConfigResolverParameterValue(
            'ng_site_api.named_queries',
            $expectedConfigurationValues,
            'ibexa_demo_site',
        );
    }

    public static function providerForTestCrossSiteaccessRoutingBoolConfigurationInvalid(): iterable
    {
        return [
            [
                'string',
                InvalidConfigurationException::class,
                'Expected "bool", but got "string"',
            ],
            [
                1,
                InvalidConfigurationException::class,
                'Expected "bool", but got "int"',
            ],
            [
                [],
                InvalidConfigurationException::class,
                'Expected "bool", but got "array"',
            ],
        ];
    }

    #[DataProvider('providerForTestCrossSiteaccessRoutingBoolConfigurationInvalid')]
    public function testCrossSiteaccessRoutingEnabledConfigurationInvalid(
        mixed $configurationValue,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $this->expectException($exceptionClass);
        $exceptionMessage = preg_quote($exceptionMessage, '/');
        $this->expectExceptionMessageMatches(sprintf('/%s/', $exceptionMessage));

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'cross_siteaccess_content' => [
                            'enabled' => $configurationValue,
                        ],
                    ],
                ],
            ],
        ]);
    }

    #[DataProvider('providerForTestCrossSiteaccessRoutingBoolConfigurationInvalid')]
    public function testCrossSiteaccessRoutingPreferMainLanguageConfigurationInvalid(
        mixed $configurationValue,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $this->expectException($exceptionClass);
        $exceptionMessage = preg_quote($exceptionMessage, '/');
        $this->expectExceptionMessageMatches(sprintf('/%s/', $exceptionMessage));

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'cross_siteaccess_content' => [
                            'prefer_main_language' => $configurationValue,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public static function provideCrossSiteaccessRoutingExternalSubtreeRootsConfigurationInvalidCases(): iterable
    {
        return [
            [
                'string',
                InvalidConfigurationException::class,
                'Expected "array", but got "string"',
            ],
            [
                [
                    'string',
                ],
                InvalidConfigurationException::class,
                'Expected "int", but got "string"',
            ],
            [
                false,
                InvalidConfigurationException::class,
                'Expected "array", but got "bool"',
            ],
            [
                [
                    true,
                ],
                InvalidTypeException::class,
                'Expected "int", but got "bool"',
            ],
        ];
    }

    #[DataProvider('provideCrossSiteaccessRoutingExternalSubtreeRootsConfigurationInvalidCases')]
    public function testCrossSiteaccessRoutingExternalSubtreeRootsConfigurationInvalid(
        mixed $configurationValue,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $this->expectException($exceptionClass);
        $exceptionMessage = preg_quote($exceptionMessage, '/');
        $this->expectExceptionMessageMatches(sprintf('/%s/', $exceptionMessage));

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'cross_siteaccess_content' => [
                            'external_subtree_roots' => $configurationValue,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public static function providerForTestCrossSiteaccessRoutingStringsConfigurationInvalid(): iterable
    {
        return [
            [
                [
                    1,
                ],
                InvalidConfigurationException::class,
                'Expected "string", but got "integer"',
            ],
            [
                1,
                InvalidConfigurationException::class,
                'Expected "array", but got "int"',
            ],
            [
                false,
                InvalidConfigurationException::class,
                'Expected "array", but got "bool"',
            ],
            [
                [
                    true,
                ],
                InvalidTypeException::class,
                'Expected "string", but got "boolean"',
            ],
        ];
    }

    #[DataProvider('providerForTestCrossSiteaccessRoutingStringsConfigurationInvalid')]
    public function testCrossSiteaccessRoutingIncludedSiteaccessesConfigurationInvalid(
        mixed $configurationValue,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $this->expectException($exceptionClass);
        $exceptionMessage = preg_quote($exceptionMessage, '/');
        $this->expectExceptionMessageMatches(sprintf('/%s/', $exceptionMessage));

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'cross_siteaccess_content' => [
                            'included_siteaccesses' => $configurationValue,
                        ],
                    ],
                ],
            ],
        ]);
    }

    #[DataProvider('providerForTestCrossSiteaccessRoutingStringsConfigurationInvalid')]
    public function testCrossSiteaccessRoutingIncludedSiteaccessGroupsConfigurationInvalid(
        mixed $configurationValue,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $this->expectException($exceptionClass);
        $exceptionMessage = preg_quote($exceptionMessage, '/');
        $this->expectExceptionMessageMatches(sprintf('/%s/', $exceptionMessage));

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'cross_siteaccess_content' => [
                            'included_siteaccess_groups' => $configurationValue,
                        ],
                    ],
                ],
            ],
        ]);
    }

    #[DataProvider('providerForTestCrossSiteaccessRoutingStringsConfigurationInvalid')]
    public function testCrossSiteaccessRoutingExcludedSiteaccessesConfigurationInvalid(
        mixed $configurationValue,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $this->expectException($exceptionClass);
        $exceptionMessage = preg_quote($exceptionMessage, '/');
        $this->expectExceptionMessageMatches(sprintf('/%s/', $exceptionMessage));

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'cross_siteaccess_content' => [
                            'excluded_siteaccesses' => $configurationValue,
                        ],
                    ],
                ],
            ],
        ]);
    }

    #[DataProvider('providerForTestCrossSiteaccessRoutingStringsConfigurationInvalid')]
    public function testCrossSiteaccessRoutingExcludedSiteaccessGroupsConfigurationInvalid(
        mixed $configurationValue,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $this->expectException($exceptionClass);
        $exceptionMessage = preg_quote($exceptionMessage, '/');
        $this->expectExceptionMessageMatches(sprintf('/%s/', $exceptionMessage));

        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'cross_siteaccess_content' => [
                            'excluded_siteaccess_groups' => $configurationValue,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public static function provideCrossSiteaccessRoutingConfigurationValidCases(): iterable
    {
        return [
            [
                false,
                [
                    'enabled' => false,
                    'external_subtree_roots' => [],
                ],
            ],
            [
                true,
                [
                    'enabled' => true,
                    'external_subtree_roots' => [],
                ],
            ],
            [
                [],
                [
                    'enabled' => true,
                    'external_subtree_roots' => [],
                ],
            ],
            [
                [
                    'enabled' => true,
                ],
                [
                    'enabled' => true,
                    'external_subtree_roots' => [],
                ],
            ],
            [
                [
                    'external_subtree_roots' => [],
                ],
                [
                    'external_subtree_roots' => [],
                    'enabled' => true,
                ],
            ],
            [
                [
                    'external_subtree_roots' => [1, 2, 3],
                ],
                [
                    'external_subtree_roots' => [1, 2, 3],
                    'enabled' => true,
                ],
            ],
            [
                [
                    'enabled' => true,
                    'external_subtree_roots' => 42,
                ],
                [
                    'enabled' => true,
                    'external_subtree_roots' => [42],
                ],
            ],
            [
                [
                    'enabled' => true,
                    'external_subtree_roots' => [1, 2, 3],
                    'included_siteaccesses' => 'sa1',
                    'included_siteaccess_groups' => 'sag1',
                    'excluded_siteaccesses' => 'sa2',
                    'excluded_siteaccess_groups' => 'sag2',
                    'prefer_main_language' => false,
                ],
                [
                    'enabled' => true,
                    'external_subtree_roots' => [1, 2, 3],
                    'included_siteaccesses' => ['sa1'],
                    'included_siteaccess_groups' => ['sag1'],
                    'excluded_siteaccesses' => ['sa2'],
                    'excluded_siteaccess_groups' => ['sag2'],
                    'prefer_main_language' => false,
                ],
            ],
            [
                [
                    'enabled' => true,
                    'external_subtree_roots' => [1, 2, 3],
                    'included_siteaccesses' => ['sa1'],
                    'included_siteaccess_groups' => ['sag1'],
                    'excluded_siteaccesses' => ['sa2'],
                    'excluded_siteaccess_groups' => ['sag2'],
                    'prefer_main_language' => false,
                ],
                [
                    'enabled' => true,
                    'external_subtree_roots' => [1, 2, 3],
                    'included_siteaccesses' => ['sa1'],
                    'included_siteaccess_groups' => ['sag1'],
                    'excluded_siteaccesses' => ['sa2'],
                    'excluded_siteaccess_groups' => ['sag2'],
                    'prefer_main_language' => false,
                ],
            ],
        ];
    }

    #[DataProvider('provideCrossSiteaccessRoutingConfigurationValidCases')]
    public function testCrossSiteaccessRoutingConfigurationValid(mixed $configurationValues, array $expectedConfigurationValues): void
    {
        $this->load([
            'system' => [
                'ibexa_demo_group' => [
                    'ng_site_api' => [
                        'cross_siteaccess_content' => $configurationValues,
                    ],
                ],
            ],
        ]);

        foreach ($expectedConfigurationValues as $key => $value) {
            $this->assertConfigResolverParameterValue(
                'ng_site_api.cross_siteaccess_content.' . $key,
                $value,
                'ibexa_demo_site',
            );
        }
    }

    protected function getContainerExtensions(): array
    {
        return [
            new IbexaCoreExtension([
                new SiteApi(),
            ]),
        ];
    }

    protected function getMinimalConfiguration(): array
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/../../Fixtures/minimal.yaml'));
    }
}
