<?php

namespace LeandroFull\AsyncTaskManager\Exceptions;

class TaskVarsException extends \Exception
{
    public function __construct(string $mesasge = '')
    {
        parent::__construct($mesasge);
    }
}