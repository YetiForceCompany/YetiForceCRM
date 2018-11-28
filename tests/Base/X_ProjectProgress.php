<?php

namespace Tests\Base;

/**
 * Class ProjectProgress.
 */
class ProjectProgress extends \Tests\Base
{
	/**
	 * ID list.
	 *
	 * @var int[]
	 */
	private static $listId;
	private static $listTaskId;
	private static $listMilestoneId;

	private static function calculateProgress(array $items): string
	{
		$hours = 0;
		$progressInHours = 0;
		foreach ($items as $item) {
			$hours += $item['h'];
			$progressInHours += ($item['h'] * (int) $item['p']) / 100;
		}
		return round((100 * $progressInHours) / $hours) . '%';
	}

	public static function createProjectRecord()
	{
		$record = \Vtiger_Record_Model::getCleanInstance('Project');
		$record->set('projectname', 'System CRM YetiForce');
		$record->save();
		return $record;
	}

	public static function createProjectMilestoneRecord(int $projectId)
	{
		$record = \Vtiger_Record_Model::getCleanInstance('ProjectMilestone');
		$record->set('projectmilestonename', 'Milestone');
		$record->set('projectmilestonedate', '2018-01-07');
		$record->set('projectid', $projectId);
		$record->save();
		return $record;
	}

	public static function createProjectTaskRecord(int $projectId, int $projectMilestoneId, int $estimatedWorkTime)
	{
		$record = \Vtiger_Record_Model::getCleanInstance('ProjectTask');
		$record->set('projecttaskname', 'Task');
		$record->set('assigned_user_id', \App\User::getCurrentUserId());
		$record->set('projectid', $projectId);
		//$record->set('projecttaskstatus', $projectId);
		$record->set('projectmilestoneid', $projectMilestoneId);
		$record->set('estimated_work_time', $estimatedWorkTime);
		//$record->set('targetenddate', );
		//$record->set('startdate', );
		$record->save();
		return $record;
	}

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass()
	{
		\App\DebugerEx::$isOn = false;
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		$projectRecordModel = static::createProjectRecord();
		static::$listId['p0'] = $projectRecordModel->getId();
		$milestoneRecordModel = static::createProjectMilestoneRecord($projectRecordModel->getId());
		static::$listMilestoneId['p0-m0'] = $milestoneRecordModel->getId();
		$taskRecordModel = static::createProjectTaskRecord($projectRecordModel->getId(), $milestoneRecordModel->getId(), 10);
		static::$listTaskId['p0-m0-t0'] = $taskRecordModel->getId();
	}

	public function testProgress()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame('0%', $projectRecordModel->get('progress'));
	}

	public function testProgressAfterUpdateTask()
	{
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p0-m0-t0']);
		$taskRecordModel->set('projecttaskprogress', '10%');
		$taskRecordModel->save();
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame('10%', $projectRecordModel->get('progress'));
	}

	public function testProgressAfterInsertNewTask()
	{
		$taskRecordModel = static::createProjectTaskRecord(static::$listId['p0'], static::$listMilestoneId['p0-m0'], 10);
		static::$listTaskId['p0-m0-t1'] = $taskRecordModel->getId();
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame('5%', $projectRecordModel->get('progress'));
		$taskRecordModel->set('projecttaskprogress', '10%');
		$taskRecordModel->save();
		$this->assertSame('10%', $projectRecordModel->get('progress'));
		$taskRecordModel->set('estimated_work_time', 20);
		$taskRecordModel->save();
		$this->assertSame(static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10]]), $projectRecordModel->get('progress'));
	}

	public function testProgressAfterInsertNewProject()
	{
		$projectRecordModel = static::createProjectRecord();
		static::$listId['p1'] = $projectRecordModel->getId();
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10]]),
			$projectRecordModelParent->get('progress')
		);
		$projectRecordModel->set('parentid', $projectRecordModelParent->getId());
		$projectRecordModel->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10]]),
			$projectRecordModelParent->get('progress')
		);
		//Create new milestone and task
		$milestoneRecordModel = static::createProjectMilestoneRecord($projectRecordModel->getId());
		static::$listMilestoneId['p1-m0'] = $milestoneRecordModel->getId();

		$taskRecordModel = static::createProjectTaskRecord($projectRecordModel->getId(), $milestoneRecordModel->getId(), 10);
		static::$listTaskId['p1-m0-t0'] = $taskRecordModel->getId();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 0]]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressAfterUpdateTaskInChild()
	{
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t0']);
		$taskRecordModel->set('projecttaskprogress', '10%');
		$taskRecordModel->save();
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10]]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressAfterInsertTaskInChild()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$taskRecordModel = static::createProjectTaskRecord($projectRecordModel->getId(), static::$listMilestoneId['p1-m0'], 50);
		static::$listTaskId['p1-m0-t1'] = $taskRecordModel->getId();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 50, 'p' => 0]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 50, 'p' => 0]]),
			$projectRecordModelParent->get('progress')
		);
		$taskRecordModel->set('projecttaskprogress', '10%');
		$taskRecordModel->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 50, 'p' => 10]]),
			$projectRecordModel->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 50, 'p' => 10]]),
			$projectRecordModelParent->get('progress')
		);
		$taskRecordModel->set('projecttaskprogress', '50%');
		$taskRecordModel->set('estimated_work_time', 55);
		$taskRecordModel->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50]]),
			$projectRecordModel->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50]]),
			$projectRecordModelParent->get('progress')
		);
		//Create new task
		static::$listTaskId['p1-m0-t2'] = static::createProjectTaskRecord(
			$projectRecordModel->getId(), static::$listMilestoneId['p1-m0'], 5
		)->getId();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 0]]),
			$projectRecordModel->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 0]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressAfterChangeProject()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t2']);
		$taskRecordModel->set('projectid', $projectRecordModelParent->getId());
		$taskRecordModel->set('projectmilestoneid', static::$listMilestoneId['p0-m0']);
		$taskRecordModel->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 0]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressAfterChangeBackProject()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t2']);
		$taskRecordModel->set('projectid', $projectRecordModel->getId());
		$taskRecordModel->set('projectmilestoneid', static::$listMilestoneId['p0-m0']);
		$taskRecordModel->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 0]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 0]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressAfterArchivedTask()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t2']);
		$taskRecordModel->changeState('Archived');
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressAfterActiveTask()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t2']);
		$taskRecordModel->changeState('Active');
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 0]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 0]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressAfterArchivedProject()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$projectRecordModel->changeState('Archived');
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressInsertTaskInArchivedProject()
	{
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t2']);
		$taskRecordModel->set('projecttaskprogress', '60%');
		$taskRecordModel->save();
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressAfterActiveProject()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$projectRecordModel->changeState('Active');
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 60]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 60]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressAfterDeleteTaskInChild()
	{
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t0']);
		$taskRecordModel->delete();
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$this->assertSame(
			static::calculateProgress([['h' => 55, 'p' => 50], ['h' => 5, 'p' => 60]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 60]]),
			$projectRecordModelParent->get('progress')
		);
	}

	public function testProgressAfterInsertAnotherNewProject()
	{
		$projectRecordModel = static::createProjectRecord();
		static::$listId['p2'] = $projectRecordModel->getId();
		$projectRecordModelChild = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 60]]),
			$projectRecordModelChild->get('progress')
		);
		//Create new milestone and task
		$milestoneRecordModel = static::createProjectMilestoneRecord($projectRecordModel->getId());
		static::$listMilestoneId['p2-m0'] = $milestoneRecordModel->getId();
		$taskRecordModel = static::createProjectTaskRecord($projectRecordModel->getId(), $milestoneRecordModel->getId(), 10);
		static::$listTaskId['p2-m0-t0'] = $taskRecordModel->getId();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 0]]),
			$projectRecordModel->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 60]]),
			$projectRecordModelChild->get('progress')
		);
		\App\DebugerEx::$isOn = true;
		$projectRecordModelChild->set('parentid', $projectRecordModel->getId());
		$projectRecordModelChild->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 60]]),
			$projectRecordModelChild->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 0], ['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 55, 'p' => 50], ['h' => 5, 'p' => 60]
			]),
			$projectRecordModel->get('progress')
		);
	}
}
