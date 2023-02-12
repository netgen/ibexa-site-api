<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\Tests\SiteAccess;

use Ibexa\Contracts\Core\Persistence\Content\Handler as ContentHandler;
use Ibexa\Contracts\Core\Persistence\Content\VersionInfo;
use Ibexa\Contracts\Core\Persistence\Handler;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Ibexa\Core\Repository\Values\Content\Location as CoreLocation;
use Netgen\Bundle\IbexaSiteApiBundle\SiteAccess\Resolver;
use Netgen\Bundle\IbexaSiteApiBundle\SiteAccess\Resolver\NativeResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use function array_filter;
use function array_pop;
use function explode;
use function in_array;

/**
 * @group siteaccess
 */
class NativeResolverTest extends TestCase
{
    public function providerForTestResolve(): array
    {
        return [
            '#1.1 Nothing matches the subtree, current siteaccess is used as a fallback' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger'],
                        ],
                    ],
                    'system' => [
                        'frontend_group' => ['tree_root' =>  4],
                        'eng' => ['languages' => ['eng-GB']],
                        'ger' => ['languages' => ['ger-DE']],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['ita-IT'],
                        ],
                    ],
                    '_error' => 'Found no siteaccesses for Location #42',
                ],
                'eng',
            ],
            '#2.1 Location is in the configured external subtree' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger'],
                    ],
                    'system' => [
                        'eng' => [
                            'languages' => ['eng-GB'],
                            'tree_root' => 4,
                        ],
                        'ger' => [
                            'languages' => ['ger-DE'],
                            'tree_root' => 8,
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'external_subtree_roots' => [8],
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['ger-DE'],
                        ],
                    ],
                ],
                'eng',
            ],
            '#3.1 Location is in the current siteaccess and its main language can be shown there' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger'],
                        ],
                    ],
                    'system' => [
                        'frontend_group' => ['tree_root' =>  8],
                        'eng' => ['languages' => ['eng-GB']],
                        'ger' => ['languages' => ['ger-DE']],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['eng-GB'],
                        ],
                    ],
                ],
                'eng',
            ],
            '#3.2 Location is in the current siteaccess and its non-main language can be shown there' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger'],
                        ],
                    ],
                    'system' => [
                        'frontend_group' => ['tree_root' =>  8],
                        'eng' => ['languages' => ['eng-GB', 'por-PT']],
                        'ger' => ['languages' => ['ger-DE']],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['ita-IT', 'por-PT'],
                        ],
                    ],
                ],
                'eng',
            ],
            '#3.3 Location is in the current siteaccess and it is always available in the main language' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger'],
                        ],
                    ],
                    'system' => [
                        'frontend_group' => ['tree_root' =>  8],
                        'eng' => ['languages' => ['eng-GB']],
                        'ger' => ['languages' => ['ger-DE']],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['ita-IT'],
                            'alwaysAvailable' => true,
                        ],
                    ],
                ],
                'eng',
            ],
            '#4.1 Siteaccess is matched by the prioritized languages of the current siteaccess' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['ita-IT', 'por-PT'],
                        ],
                    ],
                ],
                'por',
            ],
            '#4.2 Siteaccess is matched by the prioritized languages of the current siteaccess, siteaccess order is significant' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'por2', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'por2', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['ita-IT', 'por-PT'],
                        ],
                    ],
                ],
                'por',
            ],
            '#4.3 Siteaccess is matched by the prioritized languages of the current siteaccess, siteaccess order is significant' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por2', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'por2', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['ita-IT', 'por-PT'],
                        ],
                    ],
                ],
                'por2',
            ],
            '#4.4 Siteaccess is matched by the prioritized languages of the current siteaccess, language order is significant' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'ita', 'por2', 'por'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'por2', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'por-PT', 'ita-IT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['ita-IT', 'por-PT'],
                        ],
                    ],
                ],
                'por2',
            ],
            '#4.5 Siteaccess is matched by the prioritized languages of the current siteaccess, language order is significant' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por2', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'por2', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'ita-IT', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['ita-IT', 'por-PT'],
                        ],
                    ],
                ],
                'ita',
            ],
            '#4.6 Siteaccess is matched by the prioritized languages of the current siteaccess, language order is significant' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por2', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'por2', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'ita-IT', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                            'alwaysAvailable' => true,
                        ],
                    ],
                ],
                'ita',
            ],
            '#4.7 Siteaccess is matched by the prioritized languages of the current siteaccess with siteaccess exclusion' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por2', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'por2', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'ita-IT', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'excluded_siteaccesses' => ['ita'],
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                            'alwaysAvailable' => true,
                        ],
                    ],
                ],
                'por2',
            ],
            '#4.8 Siteaccess is matched by the prioritized languages of the current siteaccess with siteaccess inclusion' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por2', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'por2', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'ita-IT', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'included_siteaccesses' => ['eng', 'ger', 'por'],
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                            'alwaysAvailable' => true,
                        ],
                    ],
                ],
                'por',
            ],
            '#4.9 Siteaccess is matched by the prioritized languages of the current siteaccess with siteaccess inclusion and exclusion' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'por2', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'por2', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'ita-IT', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'included_siteaccesses' => ['eng', 'ger', 'por', 'por2'],
                        'excluded_siteaccesses' => ['por'],
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                            'alwaysAvailable' => true,
                        ],
                    ],
                ],
                'por2',
            ],
            '#4.10 Siteaccess is matched by the prioritized languages of the current siteaccess with siteaccess group exclusion' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por2', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'por2'],
                            'frontend_group_2' => ['ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'ita-IT', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'excluded_siteaccess_groups' => ['frontend_group_2'],
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                            'alwaysAvailable' => true,
                        ],
                    ],
                ],
                'por2',
            ],
            '#4.11 Siteaccess is matched by the prioritized languages of the current siteaccess with siteaccess group exclusion and siteaccess inclusion' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por2', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por'],
                            'frontend_group_2' => ['ita', 'por2'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'ita-IT', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'included_siteaccesses' => ['ger', 'por', 'por2', 'ita'],
                        'excluded_siteaccess_groups' => ['frontend_group_2'],
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                            'alwaysAvailable' => true,
                        ],
                    ],
                ],
                'por',
            ],
            '#4.12 Siteaccess is matched by the prioritized languages of the current siteaccess with siteaccess group exclusion and siteaccess inclusion' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por2', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por'],
                            'frontend_group_2' => ['ita', 'por2'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB', 'ita-IT', 'por-PT'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'por2' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'included_siteaccesses' => ['ger', 'por2', 'ita'],
                        'excluded_siteaccess_groups' => ['frontend_group_2'],
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                            'alwaysAvailable' => true,
                        ],
                    ],
                ],
                'eng',
            ],
            '#5.1 Siteaccess with the highest configured language is used' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB'],
                        ],
                        'ger' => [
                            'tree_root' =>  8,
                            'languages' => ['ger-DE', 'por-PT', 'ita-IT', 'fre-FR'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT', 'ger-DE', 'fre-FR', 'ita-IT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT', 'fre-FR', 'ita-IT', 'ger-DE'],
                        ],
                        'fre' => [
                            'tree_root' =>  8,
                            'languages' => ['fre-FR', 'ger-DE', 'por-PT', 'ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                        ],
                    ],
                ],
                'fre',
            ],
            '#5.2 Siteaccess with the highest configured language is used' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB'],
                        ],
                        'ger' => [
                            'tree_root' =>  8,
                            'languages' => ['ger-DE', 'por-PT', 'ita-IT', 'fre-FR'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT', 'ger-DE', 'fre-FR', 'ita-IT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT', 'fre-FR', 'ita-IT', 'ger-DE'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                        ],
                    ],
                ],
                'ita',
            ],
            '#5.3 Siteaccess with the highest configured language is used' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB'],
                        ],
                        'ger' => [
                            'tree_root' =>  8,
                            'languages' => ['ger-DE', 'por-PT', 'ita-IT', 'fre-FR'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT', 'ger-DE', 'fre-FR', 'ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                        ],
                    ],
                ],
                'por',
            ],
            '#5.4 Siteaccess with the highest configured language is used' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB'],
                        ],
                        'ger' => [
                            'tree_root' =>  8,
                            'languages' => ['ger-DE', 'por-PT', 'ita-IT', 'fre-FR'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                        ],
                    ],
                ],
                'ger',
            ],
            '#5.5 Siteaccess with the highest configured language is used with siteaccess exclusion' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB'],
                        ],
                        'ger' => [
                            'tree_root' =>  8,
                            'languages' => ['ger-DE', 'por-PT', 'ita-IT', 'fre-FR'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT', 'ger-DE', 'fre-FR', 'ita-IT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT', 'fre-FR', 'ita-IT', 'ger-DE'],
                        ],
                        'fre' => [
                            'tree_root' =>  8,
                            'languages' => ['fre-FR', 'ger-DE', 'por-PT', 'ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'excluded_siteaccesses' => ['fre', 'ita'],
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                        ],
                    ],
                ],
                'por',
            ],
            '#5.6 Siteaccess with the highest configured language is used with siteaccess group exclusion' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por'],
                            'frontend_group_2' => ['ita', 'fre'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB'],
                        ],
                        'ger' => [
                            'tree_root' =>  8,
                            'languages' => ['ger-DE', 'por-PT', 'ita-IT', 'fre-FR'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT', 'ger-DE', 'fre-FR', 'ita-IT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT', 'fre-FR', 'ita-IT', 'ger-DE'],
                        ],
                        'fre' => [
                            'tree_root' =>  8,
                            'languages' => ['fre-FR', 'ger-DE', 'por-PT', 'ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'excluded_siteaccess_groups' => ['frontend_group_2'],
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                        ],
                    ],
                ],
                'por',
            ],
            '#5.7 Siteaccess with the highest configured language is used with prefer main language true' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB'],
                        ],
                        'ger' => [
                            'tree_root' =>  8,
                            'languages' => ['ger-DE', 'por-PT', 'ita-IT', 'fre-FR'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT', 'ger-DE', 'fre-FR', 'ita-IT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT', 'fre-FR', 'ita-IT', 'ger-DE'],
                        ],
                        'fre' => [
                            'tree_root' =>  8,
                            'languages' => ['fre-FR', 'ger-DE', 'por-PT', 'ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR', 'ita-IT'],
                        ],
                    ],
                ],
                'fre',
            ],
            '#5.8 Siteaccess with the highest configured language is used with prefer main language false' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'ita', 'fre'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB'],
                        ],
                        'ger' => [
                            'tree_root' =>  8,
                            'languages' => ['ger-DE', 'por-PT', 'ita-IT', 'fre-FR'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT', 'ger-DE', 'fre-FR', 'ita-IT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT', 'fre-FR', 'ita-IT', 'ger-DE'],
                        ],
                        'fre' => [
                            'tree_root' =>  8,
                            'languages' => ['fre-FR', 'ger-DE', 'por-PT', 'ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR', 'ita-IT'],
                        ],
                        'prefer_main_language' => false,
                    ],
                ],
                'ita',
            ],
            '#6.1 Nothing matched, current siteaccess was found' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB'],
                        ],
                        'ger' => [
                            'tree_root' =>  4,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  4,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  4,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'excluded_siteaccess_groups' => ['frontend_group_2'],
                        'location' => [
                            'pathString' => '/1/2/4/42/',
                            'languageCodes' => ['fre-FR'],
                        ],
                    ],
                    '_error' => 'No siteaccess matched Location #42',
                ],
                'eng',
            ],
            '#6.2 Nothing matched, current siteaccess was not found' => [
                [
                    'siteaccess' => [
                        'list' => ['eng', 'ger', 'por', 'ita'],
                        'groups' => [
                            'frontend_group' => ['eng', 'ger', 'por', 'ita'],
                        ],
                    ],
                    'system' => [
                        'eng' => [
                            'tree_root' =>  4,
                            'languages' => ['eng-GB'],
                        ],
                        'ger' => [
                            'tree_root' =>  8,
                            'languages' => ['ger-DE'],
                        ],
                        'por' => [
                            'tree_root' =>  8,
                            'languages' => ['por-PT'],
                        ],
                        'ita' => [
                            'tree_root' =>  8,
                            'languages' => ['ita-IT'],
                        ],
                    ],
                    '_context' => [
                        'current_siteaccess' => 'eng',
                        'excluded_siteaccess_groups' => ['frontend_group_2'],
                        'location' => [
                            'pathString' => '/1/2/8/42/',
                            'languageCodes' => ['fre-FR'],
                        ],
                    ],
                    '_error' => 'No siteaccess matched Location #42',
                ],
                'ger',
            ],
        ];
    }

    /**
     * @dataProvider providerForTestResolve
     *
     * @throws \Exception
     */
    public function testResolve(array $data, string $expectedSiteaccessName): void
    {
        $siteaccessResolver = $this->getSiteaccessResolverUnderTest($data);
        $location = $this->getMockedLocation($data);

        self::assertSame($expectedSiteaccessName, $siteaccessResolver->resolveByLocation($location));
        self::assertSame($expectedSiteaccessName, $siteaccessResolver->resolveByLocation($location));
    }

    protected function getMockedLocation(array $data): Location
    {
        $data = $data['_context']['location'];
        $pathIds = array_filter(explode('/', $data['pathString']));

        return new CoreLocation([
            'id' => array_pop($pathIds),
            'pathString' => $data['pathString'],
            'contentInfo' => new ContentInfo([
                'id' => 24,
                'alwaysAvailable' => $data['alwaysAvailable'] ?? false,
                'mainLanguageCode' => reset($data['languageCodes'])
            ]),
        ]);
    }

    protected function getSiteaccessResolverUnderTest(array $data): Resolver
    {
        $siteaccessResolver = new NativeResolver(
            $this->persistenceHandlerMock($data),
            5,
            $this->getLoggerMock($data)
        );

        $siteaccessResolver->setConfigResolver($this->getConfigResolverMock($data));
        $siteaccessResolver->setSiteaccess($this->getSiteaccess($data));
        $siteaccessResolver->setSiteaccessGroupsBySiteaccess($this->getSiteaccessGroupsBySiteaccess($data));
        $siteaccessResolver->setSiteaccessList($this->getSiteaccessList($data));

        return $siteaccessResolver;
    }

    protected function getLoggerMock(array $data): LoggerInterface
    {
        $loggerMock = $this->createMock(LoggerInterface::class);

        if (isset($data['_error'])) {
            $loggerMock->method('error')->with($data['_error']);
        }

        return $loggerMock;
    }
    protected function persistenceHandlerMock(array $data): Handler
    {
        $versionInfo = new VersionInfo([
            'languageCodes' => $data['_context']['location']['languageCodes'],
        ]);

        $contentHandlerMock = $this->createMock(ContentHandler::class);
        $contentHandlerMock->method('loadVersionInfo')->willReturn($versionInfo);

        $persistenceHandler = $this->createMock(Handler::class);
        $persistenceHandler->method('contentHandler')->willReturn($contentHandlerMock);

        return $persistenceHandler;
    }

    protected function getConfigResolverMock(array $data): ConfigResolverInterface
    {
        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $getParameterValueMap = $this->getConfigResolverGetParameterReturnValueMap($data);

        $configResolver->method('getParameter')->willReturnMap($getParameterValueMap);

        return $configResolver;
    }

    protected function getConfigResolverGetParameterReturnValueMap(array $data): array
    {
        $siteaccessConfigMap = $this->getSiteaccessConfigMap($data);
        $valueMap = [
            [
                'ng_site_api.cross_siteaccess_content.external_subtree_roots',
                null,
                null,
                $data['_context']['external_subtree_roots'] ?? [],
            ],
            [
                'ng_site_api.cross_siteaccess_content.included_siteaccesses',
                null,
                null,
                $data['_context']['included_siteaccesses'] ?? [],
            ],
            [
                'ng_site_api.cross_siteaccess_content.included_siteaccess_groups',
                null,
                null,
                $data['_context']['included_siteaccess_groups'] ?? [],
            ],
            [
                'ng_site_api.cross_siteaccess_content.excluded_siteaccesses',
                null,
                null,
                $data['_context']['excluded_siteaccesses'] ?? [],
            ],
            [
                'ng_site_api.cross_siteaccess_content.excluded_siteaccess_groups',
                null,
                null,
                $data['_context']['excluded_siteaccess_groups'] ?? [],
            ],
            [
                'ng_site_api.cross_siteaccess_content.prefer_main_language',
                null,
                null,
                $data['_context']['prefer_main_language'] ?? true,
            ],
            [
                'ng_site_api.cross_siteaccess_content.enabled',
                null,
                null,
                true,
            ],
        ];

        foreach ($siteaccessConfigMap as $siteaccess => $config) {
            $valueMap[] = ['languages', null, $siteaccess, $config['languages'] ?? []];
            $valueMap[] = ['content.tree_root.location_id', null, $siteaccess, $config['tree_root'] ?? null];
        }

        return $valueMap;
    }

    protected function getSiteaccessConfigMap(array $data): array
    {
        $siteaccesses = $data['siteaccess']['list'];
        $map = [];

        foreach ($siteaccesses as $siteaccess) {
            $group = $this->getSiteaccessGroupBySiteaccess($siteaccess, $data);
            $siteaccessConfig = $data['system'][$siteaccess] ?? [];
            $groupConfig = $data['system'][$group] ?? [];

            $map[$siteaccess] = $siteaccessConfig + $groupConfig;
        }

        return $map;
    }

    protected function getSiteaccessGroupBySiteaccess(string $siteaccess, array $data): ?string
    {
        $groups = $data['siteaccess']['groups'] ?? [];

        foreach ($groups as $group => $groupSiteaccesses) {
            if (in_array($siteaccess, $groupSiteaccesses, true)) {
                return $group;
            }
        }

        return null;
    }

    protected function getSiteaccess(array $data): SiteAccess
    {
        return new SiteAccess($data['_context']['current_siteaccess']);
    }

    protected function getSiteaccessGroupsBySiteaccess(array $data): array
    {
        $map = [];
        $groups = $data['siteaccess']['groups'] ?? [];

        foreach ($groups as $group => $siteaccesses) {
            foreach ($siteaccesses as $siteaccess) {
                $map[$siteaccess][] = $group;
            }
        }

        return $map;
    }

    protected function getSiteaccessList(array $data): array
    {
        return $data['siteaccess']['list'];
    }
}
