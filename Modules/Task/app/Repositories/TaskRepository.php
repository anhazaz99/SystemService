<?php

namespace Modules\Task\Repositories;

use Modules\Task\app\Models\Task;

class TaskRepository
{
    public function handle() {}


    //METHOD INDEX
    public function filter(array $filter){
        $query = Task::query();
    }
}
