<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Fixpunkt Social Bridge',
    'description' => 'Shared data-transfer objects and response types bridging the fixpunkt social server and TYPO3 extensions (used by fp_social).',
    'category' => 'misc',
    'author' => 'fixpunkt für digitales GmbH',
    'author_email' => 'office@fixpunkt.com',
    'author_company' => 'fixpunkt für digitales GmbH',
    'state' => 'stable',
    'version' => '1.3.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-14.99.99',
            'php' => '8.4.0-8.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
