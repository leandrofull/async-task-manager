# Async Task Manager

If you want to perform actions in parallel so as not to impact your app navigation performance, this library can be very useful for you.

First of all, you need to install it through Composer:

<Code>composer require leandrofull/async-task-manager</Code>

## How to use

To create an async task, you must create a new Task object providing the title and the priority level for the task (from 1 to 10).


 ```
  <?php

  require_once 'vendor/autoload.php';

  $task = new LeandroFull\AsyncTaskManager\Models\Task('Title', 1);
```

After that, you need to add one or more actions to be performed on the task. If no action is added, attempting to start the task will throw an exception.

```
  ...
  $task->addAction(new LeandroFull\AsyncTaskManager\Models\TaskAction($object, $method, ...$args));
```

* *$object:* In the first parameter it is necessary to inform which object has the method that will be executed asynchronously. This way, the method (or 'the action', as I call it in this context) will work considering the values assigned to the object's properties. Important: If during the object's existence one or more 'Closure Values' are assigned to any of its properties, the task cannot be created;
* *$method:* Method (action) that will be executed;
* *$args:* Parameters that will be sent to the method upon execution.

Finally, just run the 'create' method:

```
  ...
  $task->create();
```

## How to run the tasks

There are two ways to run all tasks (remembering that they will be executed according to their priority level):
* You can simply call the 'runTasks' method of the Task Manager component.
 ```
  <?php

  require_once 'vendor/autoload.php';

  $taskManager = new LeandroFull\AsyncTaskManager\Core\TaskManager::getInstance();

  $taskManager->runTasks();
```
* Or you can call the 'run-tasks' script. It will run indefinitely, waiting for new tasks to be created before it can run them.

<code>php vendor/bin/run-tasks</code>

## Others
* LeandroFull\AsyncTaskManager\Core\TaskManager:
  * getTaskById(string $id)
  * removeTask(Task $task)
  * removeAllTasks()
  * deleteLogFile() - vendor/leandrofull/async-task-manager/var/tasks
 
* vendor/leandrofull/async-task-manager/config
  * on_task_action_execute.php: modify this file to define a response to the execution event of a task action;
  * on_task_finish.php: modify this file to define a response to the execution event off all actions in a task.
