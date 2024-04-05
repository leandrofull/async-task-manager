<?php

namespace LeandroFull\AsyncTaskManager\Core;

use LeandroFull\AsyncTaskManager\Models\Task;

abstract class AbstractTaskManager
{
    private static self $instance;

    private final function __construct() {}

    protected final function __clone() {}

    public final static function getInstance(): static
    {
        if (!isset(self::$instance)) {
            $_ENV['TASK_MANAGER'] = require __DIR__ . '/../../config.php';
            self::$instance = new ($_ENV['TASK_MANAGER']['TASK_MANAGER']);
        }
        
        return self::$instance;
    }

    abstract public function taskIdEncode(Task $task): string;

    abstract public function createTask(Task $task): void;

    abstract public function removeTask(Task $task): void;
}