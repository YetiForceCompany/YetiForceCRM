<?php
/**
 * Project progress test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

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

	/**
	 * Calculate progress.
	 *
	 * @param array $items
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore
	 */
	private static function calculateProgress(array $items): float
	{
		$hours = 0;
		$progressInHours = 0;
		foreach ($items as $item) {
			$hours += $item['h'];
			$progressInHours += ($item['h'] * (int) $item['p']) / 100;
		}
		return round((100 * $progressInHours) / $hours);
	}

	/**
	 * Create a project.
	 *
	 * @param string $name
	 *
	 * @throws \Exception
	 *
	 * @return \Vtiger_Record_Model
	 * @codeCoverageIgnore
	 */
	public static function createProjectRecord(string $name = 'System CRM YetiForce')
	{
		$record = \Vtiger_Record_Model::getCleanInstance('Project');
		$record->set('projectname', $name);
		$record->save();
		return $record;
	}

	/**
	 * Create a project milestone.
	 *
	 * @param int    $projectId
	 * @param string $name
	 *
	 * @throws \Exception
	 *
	 * @return \Vtiger_Record_Model
	 * @codeCoverageIgnore
	 */
	public static function createProjectMilestoneRecord(int $projectId, string $name = 'Milestone')
	{
		$record = \Vtiger_Record_Model::getCleanInstance('ProjectMilestone');
		$record->set('projectmilestonename', $name);
		$record->set('projectmilestonedate', '2018-01-07');
		$record->set('projectid', $projectId);
		$record->save();
		return $record;
	}

	/**
	 * Create a project task.
	 *
	 * @param int    $projectId
	 * @param int    $projectMilestoneId
	 * @param int    $estimatedWorkTime
	 * @param string $name
	 *
	 * @throws \Exception
	 *
	 * @return \Vtiger_Record_Model
	 * @codeCoverageIgnore
	 */
	public static function createProjectTaskRecord(int $projectId, int $projectMilestoneId, int $estimatedWorkTime, string $name = 'Task')
	{
		$record = \Vtiger_Record_Model::getCleanInstance('ProjectTask');
		$record->set('projecttaskname', $name);
		$record->set('assigned_user_id', \App\User::getCurrentUserId());
		$record->set('projectid', $projectId);
		$record->set('projectmilestoneid', $projectMilestoneId);
		$record->set('estimated_work_time', $estimatedWorkTime);
		$record->save();
		return $record;
	}

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass()
	{
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		$projectRecordModel = static::createProjectRecord('p0');
		static::$listId['p0'] = $projectRecordModel->getId();
		$milestoneRecordModel = static::createProjectMilestoneRecord($projectRecordModel->getId(), 'p0-m0');
		static::$listMilestoneId['p0-m0'] = $milestoneRecordModel->getId();
		$taskRecordModel = static::createProjectTaskRecord($projectRecordModel->getId(), $milestoneRecordModel->getId(), 10, 'p0-m0-t0');
		static::$listTaskId['p0-m0-t0'] = $taskRecordModel->getId();
	}

	/**
	 * Test the progress calculation.
	 */
	public function testProgress()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(0.0, $projectRecordModel->get('progress'));
	}

	/**
	 * Test the progress calculation after editing the task.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterUpdateTask()
	{
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p0-m0-t0']);
		$taskRecordModel->set('projecttaskprogress', 10);
		$taskRecordModel->save();
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(10.0, $projectRecordModel->get('progress'));
	}

	/**
	 * Test the progress calculation after adding a new task.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterInsertNewTask()
	{
		$taskRecordModel = static::createProjectTaskRecord(static::$listId['p0'], static::$listMilestoneId['p0-m0'], 10, 'p0-m0-t1');
		static::$listTaskId['p0-m0-t1'] = $taskRecordModel->getId();
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(5.0, $projectRecordModel->get('progress'));
		$taskRecordModel->set('projecttaskprogress', 10);
		$taskRecordModel->save();
		$this->assertSame(10.0, $projectRecordModel->get('progress'));
		$taskRecordModel->set('estimated_work_time', 20);
		$taskRecordModel->save();
		$this->assertSame(static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10]]), $projectRecordModel->get('progress'));
	}

	/**
	 * Test progress calculation after adding a new project.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterInsertNewProject()
	{
		$projectRecordModel = static::createProjectRecord('p1');
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
		$milestoneRecordModel = static::createProjectMilestoneRecord($projectRecordModel->getId(), 'p1-m0');
		static::$listMilestoneId['p1-m0'] = $milestoneRecordModel->getId();
		$taskRecordModel = static::createProjectTaskRecord($projectRecordModel->getId(), $milestoneRecordModel->getId(), 10, 'p1-m0-t0');
		static::$listTaskId['p1-m0-t0'] = $taskRecordModel->getId();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 0]]),
			$projectRecordModelParent->get('progress')
		);
	}

	/**
	 * Test the progress calculation after editing the task in the child.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterUpdateTaskInChild()
	{
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t0']);
		$taskRecordModel->set('projecttaskprogress', 10);
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

	/**
	 * Test the progress calculation after adding a new task in your child.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterInsertTaskInChild()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$taskRecordModel = static::createProjectTaskRecord($projectRecordModel->getId(), static::$listMilestoneId['p1-m0'], 50, 'p1-m0-t1');
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
		$taskRecordModel->set('projecttaskprogress', 10);
		$taskRecordModel->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 50, 'p' => 10]]),
			$projectRecordModel->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 50, 'p' => 10]]),
			$projectRecordModelParent->get('progress')
		);
		$taskRecordModel->set('projecttaskprogress', 50);
		$taskRecordModel->set('estimated_work_time', 60);
		$taskRecordModel->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50]]),
			$projectRecordModel->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50]]),
			$projectRecordModelParent->get('progress')
		);
		//Create new task
		static::$listTaskId['p1-m0-t2'] = static::createProjectTaskRecord(
			$projectRecordModel->getId(), static::$listMilestoneId['p1-m0'], 30, 'p1-m0-t2'
		)->getId();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 0]]),
			$projectRecordModel->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 0]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	/**
	 * Test the progress calculation after changing the project.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterChangeProject()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t2']);
		$taskRecordModel->set('projectid', $projectRecordModelParent->getId());
		$taskRecordModel->set('projectmilestoneid', static::$listMilestoneId['p0-m0']);
		$taskRecordModel->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 0]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	/**
	 * Test the progress calculation after restoring the project.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterChangeBackProject()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t2']);
		$taskRecordModel->set('projectid', $projectRecordModel->getId());
		$taskRecordModel->set('projectmilestoneid', static::$listMilestoneId['p1-m0']);
		$taskRecordModel->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 0]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 0]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	/**
	 * Test progress calculation after task archiving.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterArchivedTask()
	{
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t2']);
		$taskRecordModel->changeState('Archived');
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	/**
	 * Test progress calculation after activating the task.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterActiveTask()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t2']);
		$taskRecordModel->changeState('Active');
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 0]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 0]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	/**
	 * Test progress calculation after project archiving.
	 *
	 * @throws \Exception
	 */
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

	/**
	 * Test the progress calculation after adding the task to the archived project.
	 *
	 * @throws \Exception
	 */
	public function testProgressInsertTaskInArchivedProject()
	{
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t2']);
		$taskRecordModel->set('projecttaskprogress', 60);
		$taskRecordModel->save();
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	/**
	 * Test progress calculation after project activation.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterActiveProject()
	{
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$projectRecordModel->changeState('Active');
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 60]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 10, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 60]
			]),
			$projectRecordModelParent->get('progress')
		);
	}

	/**
	 * Test the progress calculation after removing the child's task.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterDeleteTaskInChild()
	{
		$taskRecordModel = \Vtiger_Record_Model::getInstanceById(static::$listTaskId['p1-m0-t0']);
		$taskRecordModel->delete();
		$projectRecordModel = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$this->assertSame(
			static::calculateProgress([['h' => 60, 'p' => 50], ['h' => 30, 'p' => 60]]),
			$projectRecordModel->get('progress')
		);
		$projectRecordModelParent = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 60]]),
			$projectRecordModelParent->get('progress')
		);
	}

	/**
	 * Test the progress calculation after adding another new project.
	 *
	 * @throws \Exception
	 */
	public function testProgressAfterInsertAnotherNewProject()
	{
		$projectRecordModel = static::createProjectRecord('p2');
		static::$listId['p2'] = $projectRecordModel->getId();
		$projectRecordModelChild = \Project_Record_Model::getInstanceById(static::$listId['p0']);
		$projectRecordModelChildOfChild = \Project_Record_Model::getInstanceById(static::$listId['p1']);
		$this->assertSame(
			static::calculateProgress([['h' => 60, 'p' => 50], ['h' => 30, 'p' => 60]]),
			$projectRecordModelChildOfChild->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 60]]),
			$projectRecordModelChild->get('progress')
		);
		//Create new milestone and task
		$milestoneRecordModel = static::createProjectMilestoneRecord($projectRecordModel->getId(), 'p2-m0');
		static::$listMilestoneId['p2-m0'] = $milestoneRecordModel->getId();
		$taskRecordModel = static::createProjectTaskRecord($projectRecordModel->getId(), $milestoneRecordModel->getId(), 10, 'p2-m0-t0');
		static::$listTaskId['p2-m0-t0'] = $taskRecordModel->getId();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 0]]),
			$projectRecordModel->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 60]]),
			$projectRecordModelChild->get('progress')
		);
		//Update task
		$taskRecordModel->set('projecttaskprogress', 100);
		$taskRecordModel->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 100]]),
			$projectRecordModel->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 60]]),
			$projectRecordModelChild->get('progress')
		);
		$projectRecordModelChild->set('parentid', $projectRecordModel->getId());
		$projectRecordModelChild->save();
		$this->assertSame(
			static::calculateProgress([['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 60]]),
			$projectRecordModelChild->get('progress')
		);
		$this->assertSame(
			static::calculateProgress([
				['h' => 10, 'p' => 100], ['h' => 10, 'p' => 10], ['h' => 20, 'p' => 10], ['h' => 60, 'p' => 50], ['h' => 30, 'p' => 60]
			]),
			$projectRecordModel->get('progress')
		);
	}
}
