<?php

use LeandroFull\AsyncTaskManager\Core\AbstractTaskManager;

set_time_limit(0);

require_once $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

$taskManager = AbstractTaskManager::getInstance();

echo 'Running...' . PHP_EOL;