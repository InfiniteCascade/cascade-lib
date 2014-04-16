<?php
$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->notName('LICENSE')
    ->notName('README.md')
    ->notName('composer.*')
    ->notName('phpunit.xml*')
    ->notName('*.phar')
    ->exclude('vendor')
    ->in(__DIR__)
;

return Symfony\CS\Config\Config::create()
    ->fixers(array('indentation', 'linefeed', 'trailing_spaces', 'unused_use', 'phpdoc_params', 'return', 'php_closing_tag', 'braces', 'extra_empty_lines', 'function_declaration', 'controls_spaces', 'eof_ending', 'elseif'))
    ->finder($finder)
;

?>