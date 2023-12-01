<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/lib')
    ->in(__DIR__.'/tests');

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@Symfony' => true,
        'binary_operator_spaces' => [
            'operators' => [
                '=' => 'align',
                '=>' => 'align',
            ],
        ],
        'ordered_imports' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'no_unused_imports' => false,
    ])
    ->setFinder($finder);
