<?php

namespace LeandroFull\AsyncTaskManager\Models;

use LeandroFull\AsyncTaskManager\Exceptions\TaskActionException;

class TaskAction
{
    private object $object;

    private string $method;

    private array $args;

    private bool $executed = false;

    private int $attempts = 0;

    private bool $error = false;

    private string $errorMsg = '';

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
            $this->errorMsg .= $e->getMessage() . PHP_EOL;

            if ($e::class === "ArgumentCountError" || $e::class === "TypeError" || $this->attempts > 2) {
                $this->error = true;
                $this->executed = true;
            }
        }
    }
}