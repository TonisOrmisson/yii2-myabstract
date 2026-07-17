<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

$vendor = dirname(__DIR__, 4) . '/vendor';
if (!is_file($vendor . '/autoload.php')) {
    $vendor = __DIR__ . '/../vendor';
}

if (!is_file($vendor . '/autoload.php') || !is_file($vendor . '/yiisoft/yii2/Yii.php')) {
    throw new RuntimeException('Unable to locate Composer vendor directory for MyAbstract tests.');
}

require_once $vendor . '/autoload.php';
require_once $vendor . '/yiisoft/yii2/Yii.php';
