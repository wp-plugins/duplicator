<?php

/**
 * Safely remove a directory and recursively files and directory upto multiple sublevels
 *
 * @param string $path The full path to the directory to remove
 *
 * @return bool Returns true if all content was removed
 */
function rrmdir($path)
{
    if (is_dir($path)) {
        if (($dh = opendir($path)) === false) {
            return false;
        }
        while (($object = readdir($dh)) !== false) {
            if ($object == "." || $object == "..") {
                continue;
            }
            rrmdir($path . "/" . $object);
            /*if (!rrmdir($path . "/" . $object)) {
                closedir($dh);
                return false;
            }*/
        }
        closedir($dh);
        return @rmdir($path);
    } else {
        if (is_writable($path)) {
            $result = @unlink($path);
        } else {
            $result = false;
        }

        if ($result == false) {
            echo 'Can\'t remove ' . $path . "\n";
        }
        return $result;
    }
}

$mainDir           = dirname(__DIR__) . '/';
$exclueVendorItems = [
  'requests',
  '.htaccess',
  'index.php'
];

$removeItems = [
    'composer.lock'
];

foreach ($removeItems as $item) {
    $removeItem = $mainDir . $item;
    if (!file_exists($removeItem)) {
        continue;
    }
    echo 'REMOVE ' . $mainDir . $item . "\n";
    rrmdir($mainDir . $item);
}

$vendorDir = $mainDir . 'vendor/';
if (!is_dir($vendorDir)) {
    exit(0);
}

if (($dh = opendir($vendorDir)) === false) {
    echo 'Can\'t open dir ' . $vendorDir;
    exit(1);
}

while (($file = readdir($dh)) !== false) {
    if ($file == '.' || $file == '..') {
        continue;
    }

    if (in_array($file, $exclueVendorItems)) {
        continue;
    }

    rrmdir($vendorDir . $file);
}
closedir($dh);

exit(0);
