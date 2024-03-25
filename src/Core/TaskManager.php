<?php

namespace LeandroFull\AsyncTaskManager\Core;

use LeandroFull\AsyncTaskManager\Exceptions\TaskManagerException;
use LeandroFull\AsyncTaskManager\Models\Task;

final class TaskManager extends AbstractTaskManager
{
    private function taskIdDecode(string $id): string
    {
        return base64_decode($id);
    }

    public function taskIdEncode(Task $task): string
    {
        return base64_encode($task->getPriority() . '_' . rand(1000, 1000000000) . '_' .time());
    }

    public function createTask(Task $task): void
    {
        $fileContent = null;

         if (count($task->getActions()) === 0)
            throw new TaskManagerException("No action was declared!");

        try {
            $fileContent = base64_encode(serialize($task));
        } catch(\Exception) {
            throw new TaskManagerException(
                'A serious error made it impossible to create the task. '.
                'It is likely that one of the objects inserted in the task has '.
                'properties with values ​​like \'Closure\'.
            ');
        }

        $filePath = $_ENV['TASKS_PATH'] . '/'. $task->getPriority();

        if (!is_dir($filePath))
            mkdir($filePath, 0777, true);

        file_put_contents($filePath  . '/' . $task->getId(), $fileContent);
    }

    public function removeTask(Task $task): void
    {
        $taskPath = $_ENV['TASKS_PATH'] . '/'. $task->getPriority() . '/' . $task->getId();

        if (!file_exists($taskPath)) {
            throw new TaskManagerException(
                "The specified task has not been created or has already been removed!"
            );
        }

        unlink($taskPath);
    }

    public function runTasks(): void
    {
        for ($i=1;$i<=10;$i++) {
            $tasksPath = $_ENV['TASKS_PATH'] . '/' . $i;

            if (!is_dir($tasksPath)) continue;

            $tasksDir = dir($tasksPath);

            while ($taskFileName = $tasksDir->read()) {
                if ($taskFileName === '.' || $taskFileName === '..') continue;

                $taskFilePath = $tasksPath . '/' . $taskFileName;
                $task = unserialize(base64_decode(file_get_contents($taskFilePath)));
                $actions = $task->getActions();
                $task->resetActions();

                foreach ($actions as $action) {
                    $action->execute();

                    if ($action->hadError()) {                   
                        file_put_contents(
                            $_ENV['TASKS_PATH'] . '/' . 'errors',
                            $action->getErrorMessage(),
                            FILE_APPEND
                        );

                        continue;
                    }

                    $task->addAction($action);
                }

                if (count($task->getActions()) < 1) {
                    unlink($taskFilePath);
                    continue;
                }

                file_put_contents($taskFilePath, base64_encode(serialize($task)));
            }

            $tasksDir->close();
        }
    }

    public function getTaskById(string $id): Task|false
    {
        $taskPriority = explode('_', $this->taskIdDecode($id))[0];
        $taskFilePath = $_ENV['TASKS_PATH'] . '/' . $taskPriority . '/' . $id;

        if (!file_exists($taskFilePath)) return false;

        return unserialize(base64_decode(file_get_contents($taskFilePath)));
    }
}