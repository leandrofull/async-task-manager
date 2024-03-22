<?php

namespace LeandroFull\AsyncTaskManager\Models;

use LeandroFull\AsyncTaskManager\Core\AbstractTaskManager;
use LeandroFull\AsyncTaskManager\Exceptions\TaskException;

class Task
{
    private string $id;

    private string $name;

    private int $priority;

    private int $progress = 0;

    private array $actions = [];

    private static AbstractTaskManager $taskManager;

    public function __construct(string $name, int $priority)
    {
        $this->name = trim($name);
        
        if ($priority < 1 || $priority > 10)
            throw new TaskException("Invalid priority level!");

        $this->priority = $priority;

        if (!isset(self::$taskManager))
            self::$taskManager = AbstractTaskManager::getInstance();

        $this->id = self::$taskManager->taskIdEncode($this);
    }

    public function addAction(object $object, array ...$methodsAndArgs): void
    {
        $this->actions[] =
            self::$taskManager->constroyAndGetActionToTask($object, ...$methodsAndArgs);
    }

    public function create(): void
    {
        self::$taskManager->createTask($this);
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

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function getActions(): array
    {
        return $this->actions;
    }
}