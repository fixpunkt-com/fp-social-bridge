<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->getFinder()
    ->in(__DIR__)
    ->exclude(['.build'])
;

// Enforce declare(strict_types=1) in every PHP file (TYPO3 general requirement;
// not part of the default rule set because it is a "risky" fixer).
$config->addRules([
    'declare_strict_types' => true,
]);

return $config;
