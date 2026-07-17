<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

$autoloadPaths = [
    dirname(__DIR__, 4) . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
];

$autoloadFile = null;
foreach ($autoloadPaths as $candidate) {
    if (is_file($candidate)) {
        $autoloadFile = $candidate;
        break;
    }
}

if ($autoloadFile === null) {
    throw new RuntimeException('Unable to locate Composer autoload.php for MyAbstract tests.');
}

require_once($autoloadFile);

$yiiPaths = [
    dirname(__DIR__, 4) . '/vendor/yiisoft/yii2/Yii.php',
    __DIR__ . '/../vendor/yiisoft/yii2/Yii.php',
];

$yiiFile = null;
foreach ($yiiPaths as $candidate) {
    if (is_file($candidate)) {
        $yiiFile = $candidate;
        break;
    }
}

if ($yiiFile === null) {
    throw new RuntimeException('Unable to locate Yii.php for MyAbstract tests.');
}

require_once($yiiFile);
