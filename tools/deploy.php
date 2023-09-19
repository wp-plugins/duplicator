<?php

use Duplicator\Libs\Snap\SnapIO;

echo "\n";

if (count($argv) < 2) {
    exit("Please insert deploy path\n");
}

if (!is_dir($argv[1])) {
    exit("Deploy path must be a valid folder\n");
}

require dirname(__DIR__) . '/src/Libs/Snap/SnapIO.php';

$sourcePath = SnapIO::normalizePath(dirname(__DIR__));
$destPath   = $argv[1] . '/duplicator';

if (file_exists($destPath)) {
    echo "Clean deploy dir " . $destPath . "\n";
    if (SnapIO::rrmdir($destPath) === false) {
        exit("\nCan't clean destination path\n");
    }
}

if (SnapIO::mkdirP($destPath) === false) {
    exit("\nCan\'t create " . $destPath . "\n");
}

$skipCopyItems = [
    '.git',
    '.github',
    '.gitignore',
    '.vscode',
    '.circleci',
    'nbproject',
    'bin',
    'debug',
    'tools',
    'tester',
    'tests',
    'bitbucket-pipelines.yml',
    'composer.json',
    'composer.lock',
    'phpunit.xml.dist',
    '.phpunit.result.cache',
    'installer/dup-installer/assets/images/brand',
    'dup_pro_lock.bin',
    'vendor/bin',
    'vendor/composer',
    'vendor/squizlabs',
    'vendor/autoload.php',
    'readme.md'
];

$skipCopyItems = array_map(
    function ($v) {
        return preg_quote($v, '/');
    },
    $skipCopyItems
);
$skipRegex     = '/^' . preg_quote(($sourcePath . '/'), '/') . '(' . implode('|', $skipCopyItems) . ')$/';

SnapIO::regexGlobCallback(
    $sourcePath,
    function ($path) use ($sourcePath, $destPath) {
        $newPath = $destPath . '/' . SnapIO::getRelativePath($path, $sourcePath);
        if (is_dir($path)) {
            if (SnapIO::mkdirP($newPath) === false) {
                exit("Can\'t create " . $newPath . "\n");
            }
        } else {
            if (copy($path, $newPath) === false) {
                exit("Can\'t copy " . $path . " to " .  $newPath . "\n");
            }
        }
    },
    [
        'recursive'     => true,
        'childFirst'    => false,
        'regexFile'     => $skipRegex,
        'regexFolder'   => $skipRegex,
        'invert'        => true,
        'checkFullPath' => true
    ]
);

echo "\n\nDEPLOY COMPLETE\n";
exit(0);
