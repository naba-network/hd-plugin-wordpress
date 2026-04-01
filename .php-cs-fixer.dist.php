<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('node_modules')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
    ])
    ->setFinder($finder)
;
