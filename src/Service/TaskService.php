<?php

namespace App\Service;

use App\Entity\Task;
use DateTime;

class TaskService {
    public function canEdit(Task $task): bool {
        $createDt = $task->getcreateDt();
        if($createDt == null) {
            return false;
        }
        $now = new DateTime();

        $diffInterval = date_diff($now,$createDt);

        if($diffInterval->days > 7) {
            return false;
        }
        return true;
    }
}
