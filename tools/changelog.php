<?php

require __DIR__ . '/../vendor/autoload.php';

$changelog = file_get_contents('CHANGELOG.md');

$changelog = collect(explode(PHP_EOL, $changelog));

// filter out junk

$changelog = $changelog->filter(function ($line) {
    return !empty($line) && !str_starts_with($line, '## ') && !str_starts_with($line, '* **');
});

$output = [];
$currentHeader = null;

$changelog->each(function ($line) use (&$output, &$currentHeader) {
    if (str($line)->startsWith('###')) {
        $currentHeader = $line;
        return;
    }

    if (!isset($output[$currentHeader])) {
        $output[$currentHeader] = [];
    }

    $output[$currentHeader][] = $line;
});

$changelog = collect($output)->map(function ($lines, $header) {
    return $header . PHP_EOL . PHP_EOL . implode(PHP_EOL, $lines) . PHP_EOL;
})->implode(PHP_EOL);

file_put_contents('CHANGELOG.md', $changelog);
