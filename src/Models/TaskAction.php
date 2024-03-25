<?php

namespace LeandroFull\AsyncTaskManager\Models;

use LeandroFull\AsyncTaskManager\Exceptions\TaskActionException;

class TaskAction
{
    private object $object;

    private string $method;

    private array $args;

    private bool $executed = false;

    private bool $error = false;

    private int $attempts = 0;

    private string $errorMessage;

    public function __construct(object $object, string $method, mixed ...$args)
    {
        $this->object = $object;
        $this->method = trim($method);
        $this->args = $args;

        if (!method_exists($object, $this->method)) {
            throw new TaskActionException(
                'The method "'.$this->method.'" does not exist in "'.$this->object::class.'"'
            );
        }
    }

    public function execute(): void
    {
        $this->attempts++;

        try {
            $this->object->{$this->method}(...$this->args);
            $this->executed = true;
        } catch(\Throwable $e) {
            if ($e::class === "ArgumentCountError" || $e::class === "TypeError" || $this->attempts > 2) {
                $this->errorMessage = $e->getMessage();
                $this->executed = true;
                $this->error = true;
            }
        }
    }

    public function wasExecuted(): bool
    {
        return $this->executed;
    }

    public function hadError(): bool
    {
        return $this->error;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}