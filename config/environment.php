<?php
// Environment variables take precedence over private local configuration.
$localConfigPath = __DIR__ . '/local.php';
if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;
    foreach ($localConfig as $key => $value) {
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
    unset($localConfig, $key, $value);
}
unset($localConfigPath);