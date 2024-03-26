<?php

namespace LeandroFull\AsyncTaskManager\Core;

use LeandroFull\AsyncTaskManager\Models\Task;

abstract class AbstractTaskManager
{
    private static self $instance;

    private final function __construct()
    {
        if (!isset($_ENV['TASKS_PATH'])) {
            $_ENV['TASKS_PATH'] = require __DIR__ . '/../../config/tasks_path.php';
            $_ENV['ON_TASK_ACTION_EXECUTE'] = require __DIR__ . '/../../config/on_task_action_execute.php';
            $_ENV['ON_TASK_FINISH'] = require __DIR__ . '/../../config/on_task_finish.php';
        }
    }

    protected final function __clone() {}

    public final static function getInstance(): static
    {
        if (!isset(self::$instance))
            self::$instance = new (require __DIR__ . '/../../config/task_manager.php');
        
        return self::$instance;
    }

    abstract public function taskIdEncode(Task $task): string;

    abstract public function createTask(Task $task): void;

    abstract public function removeTask(Task $task): void;

    abstract public function removeAllTasks(): void;

    abstract public function runTasks(): void;

    abstract public function getTaskById(string $id): Task|false;
}