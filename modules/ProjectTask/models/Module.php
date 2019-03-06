<?php
 /* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
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
		$query = new \App\Db\Query();
		if (!$user) {
			$user = (int) App\User::getCurrentUserModel()->getId();
		}
		$query->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.smownerid', 'vtiger_crmentity.setype', 'vtiger_projecttask.*'])
			->from('vtiger_projecttask')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_projecttask.projecttaskstatus' => $params['projecttaskstatus']]);

		if (isset($params['projecttaskpriority'])) {
			$query->andWhere(['vtiger_projecttask.projecttaskpriority' => $params['projecttaskpriority']]);
		}
		\App\PrivilegeQuery::getConditions($query, 'ProjectTask');
		if ($user !== 'all' && !empty($user)) {
			$subQuery = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->innerJoin('vtiger_projecttask', 'u_#__crmentity_showners.crmid=vtiger_projecttask.projecttaskid')->where(['userid' => $user])->distinct('crmid');
			$query->andWhere(['or', ['vtiger_crmentity.smownerid' => $user], ['vtiger_crmentity.crmid' => $subQuery]]);
		}
		$query->limit($pagingModel->getPageLimit() + 1)->offset($pagingModel->getStartIndex());
		$dataReader = $query->createCommand()->query();
		$projectTasks = [];
		while ($row = $dataReader->read()) {
			$model = Vtiger_Record_Model::getCleanInstance('ProjectTask');
			$model->setData($row);
			$model->setId($row['crmid']);
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
