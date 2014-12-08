<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
	
	/**
	 * Time based Queue of tasks ready for execution.
	 *
	 */
	class VTTaskQueue{
		
		public function __construct($adb){
			$this->adb = $adb;
		}
		
		/**
		 * Queue a task for execution.
		 *
		 * @param $taskId The id of the task to queue
		 * @param $entityId The id of the crm entity the task is assiciated with.
		 * @param $when The time after which the task should be executed. This is 
		 *        an optional value with a default value of 0.
		 */
		public function queueTask($taskId, $entityId, $when=0, $taskContents = false){
			$adb = $this->adb;
			$adb->pquery('INSERT INTO com_vtiger_workflowtask_queue (task_id, entity_id, do_after, task_contents) VALUES(?, ?, ?, ?)',
																array($taskId, $entityId, $when, $taskContents));
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
		public function getReadyTasks(){
			$adb = $this->adb;
			$time = time();
			$result = $adb->pquery('SELECT task_id, entity_id, task_contents FROM com_vtiger_workflowtask_queue WHERE do_after<?', array($time));
			$it =  new SqlResultIterator($adb, $result);
			$arr = array();
			foreach($it as $row){
				$arr[]=array($row->task_id, $row->entity_id, $row->task_contents);
			}
			$adb->pquery("delete from com_vtiger_workflowtask_queue where do_after<?", array($time));
			return $arr;
		}
		
	}
?>