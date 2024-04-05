<?php

use LeandroFull\AsyncTaskManager\Models\TaskAction;

return [
    'TASKS_PATH' => __DIR__ . '/var/tasks',
    'TASK_MANAGER' => LeandroFull\AsyncTaskManager\Core\TaskManager::class,
    'EVENT_LISTENERS' => [
        'ON_ACTION_EXECUTE' => static function(string $taskId, TaskAction $taskAction) {},
        'ON_TASK_FINISH' => static function(string $taskId, array $taskActions) {}
    ]
];