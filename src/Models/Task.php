<?php

namespace LeandroFull\AsyncTaskManager\Models;

use LeandroFull\AsyncTaskManager\Core\AbstractTaskManager;
use LeandroFull\AsyncTaskManager\Exceptions\TaskException;

class Task
{
    private string $id;

    private string $name;

    private int $priority;

    private array $actions = [];

    private static AbstractTaskManager $taskManager;

    public function __construct(string $name, int $priority)
    {
        $this->name = trim($name);
        $this->priority = $priority;

        if (empty($this->name))
            throw new TaskException('The task name cannot be empty');
        
        if ($priority < 1 || $priority > 10)
            throw new TaskException("Invalid priority level!");

        if (!isset(self::$taskManager))
            self::$taskManager = AbstractTaskManager::getInstance();

        $this->id = self::$taskManager->taskIdEncode($this);
    }

    public function addAction(TaskAction $taskAction): void
    {
        $this->actions[] = $taskAction;
    }

    public function resetActions(): void
    {
        $this->actions = [];
    }

    public function create(): void
    {
        self::$taskManager->createTask($this);
    }

    public function remove(): void
    {
        self::$taskManager->removeTask($this);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getActions(): array
    {
        return $this->actions;
    }
}