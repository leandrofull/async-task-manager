<?php

namespace LeandroFull\AsyncTaskManager\Exceptions;

class TaskManagerException extends \Exception
{
    public function __construct(string $mesasge = '')
    {
        parent::__construct($mesasge);
    }
}