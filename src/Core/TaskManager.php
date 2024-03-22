<?php

namespace LeandroFull\AsyncTaskManager\Core;

use LeandroFull\AsyncTaskManager\Exceptions\TaskManagerException;
use LeandroFull\AsyncTaskManager\Models\Task;

final class TaskManager extends AbstractTaskManager
{
    private function runTask(array $actions): void
    {
        foreach ($actions as $action) {
            $object = unserialize($action["object"]);
            foreach ($action["methods_and_args"] as $methodAndArgs) {
                foreach ($methodAndArgs["args"] as $argKey => $arg) {
                    if (gettype($arg) === 'string' && str_starts_with($arg, '@#OBJECT#@')) {
                        $methodAndArgs["args"][$argKey] = 
                            unserialize(str_replace('@#OBJECT#@', '', $arg));
                    }
                }
                $object->{$methodAndArgs["method"]}(...$methodAndArgs["args"]);
            }
        }
    }

    private function taskIdDecode(string $id): string
    {
        return base64_decode($id);
    }

    public function taskIdEncode(Task $task): string
    {
        return base64_encode($task->getPriority() . '_' . rand(1000, 1000000000) . '_' .time());
    }

    public function constroyAndReturnATaskAction(object $object, array ...$methodsAndArgs): array
    {
        if (count($methodsAndArgs) === 0)
            throw new TaskManagerException("No action was declared!");

        foreach ($methodsAndArgs as $methodAndArgsKey => $methodAndArgs) {
            if (count($methodAndArgs) === 0)
                throw new TaskManagerException(
                    "No action was declared in argument ".($methodAndArgsKey+2)."!"
                );

            if (gettype($methodAndArgs[0]) !== 'string') {
                throw new TaskManagerException(
                    "The first position of the methods and arguments array must be of type 'string'"
                );
            }

            $method = array_shift($methodAndArgs);
            $args = $methodAndArgs;

            foreach ($args as $argKey => $arg) {
                if (gettype($arg) === 'object')
                    $arg = "@#OBJECT#@".serialize($arg);

                $args[$argKey] = $arg;
            }

            $methodsAndArgs[$methodAndArgsKey] = ["method" => $method, "args" => $args];
        }

        return [
            "object" => serialize($object),
            "methods_and_args" => $methodsAndArgs
        ];
    }

    public function createTask(Task $task): void
    {
        $taskActions = $task->getActions();

        if (count($taskActions) === 0)
            throw new TaskManagerException("No action was declared!");

        $fileName = $task->getId();
        $subDir = $task->getPriority();

        $fileContent = base64_encode(json_encode(([
            "id" => $fileName,
            "name" => $task->getName(),
            "priority" => $subDir,
            "progress" => $task->getProgress(),
            "actions" => $taskActions
        ])));

        $filePath = $_ENV['TASKS_PATH'] . '/'. $subDir;

        if (!is_dir($filePath))
            mkdir($filePath, 0777, true);

        file_put_contents($filePath  . '/' . $fileName, $fileContent);
    }

    public function removeTask(Task $task): void
    {
        $taskPath = $_ENV['TASKS_PATH'] . '/'. $task->getPriority();

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
                $taskFileContent = json_decode(base64_decode(file_get_contents($taskFilePath)), true);

                try {
                    $this->runTask($taskFileContent["actions"]);
                } catch(\Throwable $e) {
                    echo $e->getMessage() . PHP_EOL;
                }
            }
        }
    }

    public function getTaskById(string $id): Task|false
    {
        $taskPriority = explode('_', $this->taskIdDecode($id))[0];
        $taskFilePath = $_ENV['TASKS_PATH'] . '/' . $taskPriority . '/' . $id;

        if (!file_exists($taskFilePath)) return false;

        $taskFileContent = json_decode(base64_decode(file_get_contents($taskFilePath)), true);
        $reflection = new \ReflectionClass(Task::class);
        $task = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('id')->setValue($task, $taskFileContent["id"]);
        $reflection->getProperty('name')->setValue($task, $taskFileContent["name"]);
        $reflection->getProperty('priority')->setValue($task, $taskFileContent["priority"]);
        $reflection->getProperty('progress')->setValue($task, $taskFileContent["progress"]);
        $reflection->getProperty('actions')->setValue($task, $taskFileContent["actions"]);
        $reflection->getProperty('taskManager')->setValue($this);
        return $task;
    }
}