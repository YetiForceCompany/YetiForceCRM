<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

/**
 * Time based Queue of tasks ready for execution.
 */
class VTTaskQueue
{
	/**
	 * Queue a task for execution.
	 *
	 * @param $taskId The id of the task to queue
	 * @param $entityId The id of the crm entity the task is assiciated with
	 * @param $when The time after which the task should be executed. This is
	 *        an optional value with a default value of 0
	 * @param mixed $taskContents
	 */
	public function queueTask($taskId, $entityId, $when = 0, $taskContents = false)
	{
		\App\Db::getInstance()->createCommand()->insert('com_vtiger_workflowtask_queue', [
			'task_id' => $taskId,
			'entity_id' => $entityId,
			'do_after' => $when,
			'task_contents' => $taskContents,
		])->execute();

		return true;
	}

	/**
	 * Get a list of taskId/entityId pairs ready for execution.
	 *
	 * The method fetches task id/entity id where the when timestamp
	 * is less than the current time when the method was called.
	 *
	 * @return A list of pairs of the form array(taskId, entityId)
	 */
	public function getReadyTasks()
	{
		$time = time();
		$query = (new \App\Db\Query())->select(['task_id', 'entity_id', 'task_contents'])->from('com_vtiger_workflowtask_queue')->andWhere(['<', 'do_after', $time]);
		$arr = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$arr[] = [$row['task_id'], $row['entity_id'], $row['task_contents']];
		}
		\App\Db::getInstance()->createCommand()->delete('com_vtiger_workflowtask_queue', ['<', 'do_after', $time])->execute();

		return $arr;
	}
}
