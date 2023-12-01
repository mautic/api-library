<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/lib')
    ;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'               => true,
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align',
                '='  => 'align',
            ],
        ],
        'phpdoc_to_comment' => false,
        'ordered_imports'   => true,
        'array_syntax'      => [
            'syntax' => 'short',
        ],
        'no_unused_imports' => true,
        /**
         * Our templates rely heavily on things like endforeach, endif, etc.
         * This setting should be turned off at least until we've switched to Twig
         * (which is required for Symfony 5).
         */
        'no_alternative_syntax' => false,
        'phpdoc_summary'        => false,
        'yoda_style'            => false,
    ])
    ->setFinder($finder);
