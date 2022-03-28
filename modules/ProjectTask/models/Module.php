<?php

 /* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class ProjectTask_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Get project tasks by status.
	 *
	 * @param array               $params
	 * @param Vtiger_Paging_Model $pagingModel
	 * @param mixed               $user
	 *
	 * @return array
	 */
	public static function getRecordsByStatus(array $params, Vtiger_Paging_Model $pagingModel, $user): array
	{
		if (!$user) {
			$user = \App\User::getCurrentUserId();
		}
		$queryGenerator = new App\QueryGenerator('ProjectTask');
		$queryGenerator->setFields(['id', 'projecttaskname', 'projecttaskprogress', 'projectid', 'projectmilestoneid', 'targetenddate', 'enddate']);
		$queryGenerator->addNativeCondition(['projecttaskstatus' => $params['projecttaskstatus']]);
		if (isset($params['projecttaskpriority'])) {
			$queryGenerator->addNativeCondition(['projecttaskpriority' => $params['projecttaskpriority']]);
		}
		if ('all' !== $user && !empty($user)) {
			$queryGenerator->addCondition('shownerid', $user, 'e', false);
			$queryGenerator->addCondition('assigned_user_id', $user, 'e', false);
		}
		$queryGenerator->setLimit($pagingModel->getPageLimit() + 1);
		$queryGenerator->setOffset($pagingModel->getStartIndex());
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$projectTasks = [];
		while ($row = $dataReader->read()) {
			$model = Vtiger_Record_Model::getCleanInstance('ProjectTask');
			$model->setData($row);
			$model->setId($row['id']);
			$projectTasks[] = $model;
		}
		$pagingModel->calculatePageRange($dataReader->count());
		if ($dataReader->count() > $pagingModel->getPageLimit()) {
			array_pop($projectTasks);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		$dataReader->close();
		return $projectTasks;
	}
}
