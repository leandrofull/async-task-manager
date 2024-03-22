<?php

namespace LeandroFull\AsyncTaskManager\Exceptions;

class TaskException extends \Exception
{
    public function __construct(string $mesasge = '')
    {
        parent::__construct($mesasge);
    }
}