<?php

/**
 * Project Milestone record model Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class ProjectMilestone_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function to save record.
	 */
	public function saveToDb()
	{
		parent::saveToDb();
		$this->updateTasks();
	}

	/**
	 * Updates tasks when project has been changed.
	 *
	 * @return void
	 */
	private function updateTasks()
	{
		if ($this->getPreviousValue('projectid')) {
			$queryGenerator = new \App\QueryGenerator('ProjectTask');
			$queryGenerator->addNativeCondition(['projectid' => $this->getPreviousValue('projectid')]);
			$queryGenerator->setFields(['id']);
			$queryGenerator->permissions = false;
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();
			while ($row = $dataReader->read()) {
				$recordModel = Vtiger_Record_Model::getInstanceById($row['id'], 'ProjectTask');
				$recordModel->set('projectid', $this->get('projectid'));
				$recordModel->save();
			}
		}
	}
}
