<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Fixpunkt Social Bridge',
    'description' => 'Shared data-transfer objects and response types bridging the fixpunkt social server and TYPO3 extensions (used by fp_social).',
    'category' => 'misc',
    'author' => 'Yannik Börgener',
    'author_company' => 'fixpunkt für digitales GmbH',
    'state' => 'stable',
    'version' => '1.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'php' => '8.1.0-8.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
