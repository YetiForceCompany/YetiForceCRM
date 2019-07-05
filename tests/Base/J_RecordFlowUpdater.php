<?php
/**
 * The file contains: the test class for RecordFlowUpdater.
 *
 * @package Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Base;

use App\Automatic\RecordFlowUpdater;

/**
 * The test class for RecordFlowUpdater.
 */
class J_RecordFlowUpdater extends \Tests\Base
{
	/**
	 * List of Vtiger_Record_Model.
	 *
	 * @var Vtiger_Record_Model[]
	 */
	private static $project = [];

	/**
	 * List of Vtiger_Record_Model.
	 *
	 * @var Vtiger_Record_Model[]
	 */
	private static $projectMilestone = [];

	/**
	 * List of Vtiger_Record_Model.
	 *
	 * @var Vtiger_Record_Model[]
	 */
	private static $projectTask = [];

	/**
	 * Testing configurations.
	 *
	 * @return void
	 */
	public function testConfigurations()
	{
		\Vtiger_Cache::$cacheEnable = false;
		$taskFlowUpdater = new RecordFlowUpdater('ProjectTask');
		$this->assertTrue($taskFlowUpdater->checkIsConfigured(), 'The RecordFlowUpdater for ProjectTask is not configured');
	}

	/**
	 * Testing the status after creating the project.
	 *
	 * @return void
	 */
	public function testCreateProject()
	{
		$projectModel = $this->createProject('p0', 'PLL_PLANNED');
		$this->assertProject('PLL_PLANNED', $projectModel);
	}

	/**
	 * Testing the status after creating the milestone.
	 *
	 * @return void
	 */
	public function testCreateProjectMilestone()
	{
		$milestoneModel = $this->createProjectMilestone('pm0', 'PLL_PLANNED', $this->getProject('p0')->getId());
		$this->assertProjectMilestone('PLL_PLANNED', $milestoneModel);
		$this->assertProject('PLL_PLANNED', $this->getProject('p0'));
	}

	/**
	 * Testing the status after creating the task.
	 *
	 * @return void
	 */
	public function testCreateProjectTask()
	{
		$taskModel = $this->createProjectTask('pt0', 'PLL_PLANNED', $this->getProject('p0')->getId(), $this->getProjectMilestone('pm0')->getId());
		$this->assertProjectTask('PLL_PLANNED', $taskModel);
		$this->assertProject('PLL_PLANNED', $this->getProject('p0'));
		$this->assertProjectMilestone('PLL_PLANNED', $this->getProjectMilestone('pm0'));
	}

	/**
	 * Testing the status after creating several task.
	 *
	 * @return void
	 */
	public function testCreateProjectSeveralTask()
	{
		for ($i = 1; $i <= 5; ++$i) {
			$taskModel = $this->createProjectTask('pt' . $i, 'PLL_PLANNED', $this->getProject('p0')->getId(), $this->getProjectMilestone('pm0')->getId());
			$this->assertProjectTask('PLL_PLANNED', $taskModel);
		}
		$this->assertProject('PLL_PLANNED', $this->getProject('p0'));
		$this->assertProjectMilestone('PLL_PLANNED', $this->getProjectMilestone('pm0'));
	}

	/**
	 * Testing the status of PLL_IN_PROGRESSING.
	 *
	 * @return void
	 */
	public function testShouldBeInProgressing()
	{
		$taskModel = $this->getProjectTask('pt3');
		$taskModel->set('projecttaskstatus', 'PLL_IN_PROGRESSING');
		$taskModel->save();
		$this->executeBatchMethod();
		$this->assertProjectTask('PLL_IN_PROGRESSING', $taskModel);
		$this->assertProjectMilestone('PLL_IN_PROGRESSING', $this->getProjectMilestone('pm0'));
		$this->executeBatchMethod();
	}

	/**
	 * Execute batch method.
	 *
	 * @return void
	 */
	private function executeBatchMethod()
	{
		require 'cron/BatchMethods.php';
	}

	/**
	 * Assert project task.
	 *
	 * @param string               $expectedStatus
	 * @param \Vtiger_Record_Model $taskModel
	 *
	 * @return void
	 */
	private function assertProjectTask(string $expectedStatus, \Vtiger_Record_Model $taskModel)
	{
		$this->assertSame($expectedStatus, $taskModel->get('projecttaskstatus'), 'ProjectTask Id: ' . $taskModel->getId());
	}

	/**
	 * Assert project milestone.
	 *
	 * @param string                         $expectedStatus
	 * @param \ProjectMilestone_Record_Model $milestoneModel
	 *
	 * @return void
	 */
	private function assertProjectMilestone(string $expectedStatus, \ProjectMilestone_Record_Model $milestoneModel)
	{
		$this->assertSame($expectedStatus, $milestoneModel->get('projectmilestone_status'), 'ProjectMilestone Id: ' . $milestoneModel->getId());
	}

	/**
	 * Assert project.
	 *
	 * @param string                $expectedStatus
	 * @param \Project_Record_Model $projectModel
	 *
	 * @return void
	 */
	private function assertProject(string $expectedStatus, \Project_Record_Model $projectModel)
	{
		$this->assertSame($expectedStatus, $projectModel->get('projectstatus'), 'Project Id: ' . $projectModel->getId());
	}

	/**
	 * Create project task.
	 *
	 * @param string   $name
	 * @param string   $status
	 * @param int|null $projectId
	 * @param int|null $projectMilestoneId
	 *
	 * @return void
	 */
	private function createProjectTask(string $name, string $status, ?int $projectId = null, ?int $projectMilestoneId = null)
	{
		$record = \Vtiger_Record_Model::getCleanInstance('ProjectTask');
		$record->set('projectmilestonename', $name);
		$record->set('projecttaskstatus', $status);
		$record->set('projectid', $projectId);
		$record->set('projectmilestoneid', $projectMilestoneId);
		$record->save();
		static::$projectTask[$name] = $record->getId();
		return $record;
	}

	/**
	 * Create project milestone.
	 *
	 * @param string   $name
	 * @param string   $status
	 * @param int|null $projectId
	 *
	 * @return void
	 */
	private function createProjectMilestone(string $name, string $status, ?int $projectId = null)
	{
		$record = \Vtiger_Record_Model::getCleanInstance('ProjectMilestone');
		$record->set('projectmilestonename', $name);
		$record->set('projectmilestone_status', $status);
		$record->set('projectid', $projectId);
		$record->save();
		static::$projectMilestone[$name] = $record->getId();
		return $record;
	}

	/**
	 * Create project.
	 *
	 * @param string $name
	 * @param string $status
	 *
	 * @return void
	 */
	private function createProject(string $name, string $status)
	{
		$record = \Vtiger_Record_Model::getCleanInstance('Project');
		$record->set('projectname', $name);
		$record->set('projectstatus', $status);
		$record->save();
		static::$project[$name] = $record->getId();
		return $record;
	}

	/**
	 * Get project.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	private function getProject(string $name): \Project_Record_Model
	{
		if (empty(static::$project[$name])) {
			throw new \Exception('getProject: ' . $name);
		}
		\App\Cache::staticDelete('RecordModel', static::$project[$name] . ':Project');
		return \Vtiger_Record_Model::getInstanceById(static::$project[$name], 'Project');
	}

	/**
	 * Get project milestone.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	private function getProjectMilestone(string $name): \ProjectMilestone_Record_Model
	{
		if (empty(static::$projectMilestone[$name])) {
			throw new \Exception('getProjectMilestone: ' . $name);
		}
		\App\Cache::staticDelete('RecordModel', static::$project[$name] . ':ProjectMilestone');
		return \Vtiger_Record_Model::getInstanceById(static::$projectMilestone[$name], 'ProjectMilestone');
	}

	/**
	 * Get project task.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	private function getProjectTask(string $name): \Vtiger_Record_Model
	{
		if (empty(static::$projectTask[$name])) {
			throw new \Exception('getProjectTask: ' . $name);
		}
		return \Vtiger_Record_Model::getInstanceById(static::$projectTask[$name], 'ProjectTask');
	}
}
