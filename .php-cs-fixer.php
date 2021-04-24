<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src')
    ->in('tests');

$config = new PhpCsFixer\Config();

return $config->setRules(
    [
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'cast_spaces' => true,
        'no_unused_imports' => true,
        'no_useless_return' => true,
        'single_quote' => true,
    ]
)
    ->setFinder($finder);
