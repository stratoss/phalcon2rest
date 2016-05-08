<?php

use Phalcon\Loader;

/**
 * By default, namespaces are assumed to be the same as the path.
 * This function allows us to assign namespaces to alternative folders.
 * It also puts the classes into the PSR-0 autoLoader.
 */
$loader = new Loader();
$composerNamespacesPath = __DIR__ . '/vendor/composer/autoload_psr4.php';
if (file_exists($composerNamespacesPath) && is_file($composerNamespacesPath)) {
    $composerNamespacesRaw = include($composerNamespacesPath);
    $composerNamespaces = [];
    foreach ($composerNamespacesRaw as $namespace => $path) {
        $composerNamespaces[rtrim($namespace, '\\')] = $path[0];
    }
} else {
    $composerNamespaces = [];
}

$composerAutoloadFilesPath = __DIR__ . '/vendor/composer/autoload_files.php';
if (file_exists($composerAutoloadFilesPath) && is_file($composerAutoloadFilesPath)) {
    $allFiles = include($composerAutoloadFilesPath);
    foreach ($allFiles as $file) {
        include($file);
    }
}
$namespaces = array_merge([
    'Phalcon2Rest\Exceptions' => __DIR__ . '/Exceptions/',
    'Phalcon2Rest\Responses' => __DIR__ . '/Responses/',
    'Phalcon2Rest\Components' => __DIR__ . '/Components/',
    'Phalcon2Rest\Modules' => __DIR__ . '/Modules/',
    'Phalcon2Rest\Models' => __DIR__ . '/Models/'
], $composerNamespaces);
$loader->registerNamespaces($namespaces)->register();