<?php

echo "\n";

if (count($argv) < 3) {
    exit("Use param [PLUGIN PATH] [VERSION]\n");
}

if (strlen($argv[2]) == 0) {
    exit("New version can\'t b empty\n");
}

if (!is_dir($argv[1])) {
    exit("Deploy path must be a valid folder\n");
}

$newVersion = $argv[2];
$folder     = realpath(rtrim($argv[1], '\\/')) . '/';
$replaces   = [
    [
        'file'    => 'define.php',
        'regex'   => '/(define\s*\(\s*[\'"]DUPLICATOR_VERSION[\'"]\s*,\s*["\'])(.+)([\'"]\s*\)\s*;)/',
        'replace' => '${1}' . $newVersion . '${3}',
        'content' => ''
    ],
    [
        'file'    => 'installer/dup-installer/main.installer.php',
        'regex'   => '/(define\s*\(\s*[\'"]DUPX_VERSION[\'"]\s*,\s*["\'])(.+)([\'"]\s*\)\s*;)/',
        'replace' => '${1}' . $newVersion . '${3}',
        'content' => ''
    ],
    [
        'file'    => 'duplicator.php',
        'regex'   => '/(\s*\*\s+Version:\s*)(.+)/',
        'replace' => '${1}' . $newVersion,
        'content' => ''
    ],
    [
        'file'    => 'readme.txt',
        'regex'   => '/(\s*Stable\s+tag:\s*)(.+)/',
        'replace' => '${1}' . $newVersion,
        'content' => ''
    ]
];

$error = false;

echo "SCAN FOLDER " . $folder . "\n";

foreach ($replaces as $index => $replace) {
    $file = $folder . $replace['file'];
    if (!file_exists($file) || !is_writable($file)) {
        echo "Plugin file " . $replace['file'] . " don't exists or isn't writeable\n";
        $error = true;
        continue;
    }

    if (($content = file_get_contents($file)) === false) {
        echo "Cant read file " . $replace['file'] . "\n";
        $error = true;
        continue;
    }

    if (preg_match($replace['regex'], $content) !== 1) {
        echo "Invalid content in file " . $replace['file'] . ", version not found\n";
        $error = true;
        continue;
    }

    $replaces[$index]['content'] = $content;
}

if ($error) {
    exit("\nThe plugin folder is invalid, exit without making replacement\n");
}

foreach ($replaces as $index => $replace) {
    $file       = $folder . $replace['file'];
    $newContent = preg_replace($replace['regex'], $replace['replace'], $replace['content']);

    if (file_put_contents($file, $newContent) === false) {
        echo "Update file " . $replace['file'] . " failed\n";
    } else {
        echo "File " . $replace['file'] . " updated to version " . $newVersion . "\n";
    }
}

echo "VERSION UPDATE COMPLETE\n";
