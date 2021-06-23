<?php declare(strict_types=1);
$header = <<<'EOF'
This file is part of ksaveras/circuit-breaker.

(c) Ksaveras Sakys <xawiers@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__.'/src', __DIR__.'/tests'])
;

$config = new PhpCsFixer\Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => false,
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'continue',
                'return',
                'switch',
                'throw',
                'try',
            ],
        ],
        'declare_strict_types' => true,
        'fopen_flags' => true,
        'header_comment' => ['header' => $header, 'separate' => 'none'],
        'linebreak_after_opening_tag' => false,
        'method_chaining_indentation' => true,
        'no_useless_else' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'ordered_imports' => true,
        'php_unit_mock' => true,
        'protected_to_private' => false,
    ])
    ->setFinder($finder);
