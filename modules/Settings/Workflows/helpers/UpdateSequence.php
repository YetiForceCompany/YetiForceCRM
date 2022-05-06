<?php

/**
 * Settings Workflows update sequence helper file.
 *
 * @package   Settings.Helper
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Settings Workflows update sequence helper class.
 */
class Settings_Workflows_UpdateSequence_Helper
{
	/** @var string Base table name */
	public $baseTable = 'com_vtiger_workflows';
    /** @var string Base table index column name */
	public $baseIndex = 'workflow_id';
	/** @var string|bool Sort type */
	private $sortType = false;
	/** @var int Page number */
	private $pageNumber = 0;
	/** @var int Entries per page */
	private $entriesPerPage = 0;
	/** @var array Workflows for update */
	private $workflowsOrder = [];
	 /** @var string Module name */
	private $moduleName = '';

	/**
	 * Construct function
	 *
	 * @param array $workflowsOrder
	 * @param int $pageNumber
	 * @param bool|string $sortType
	 * @param string $moduleName
	 */
	public function __construct(array $workflowsOrder, int $pageNumber, $sortType, string $moduleName)
	{
		$this->workflowsOrder = $workflowsOrder;
		$this->pageNumber = $pageNumber;
		$this->sortType = $sortType;
		$this->moduleName = $moduleName;
		$this->entriesPerPage = \App\Config::main('list_max_entries_per_page');
		$this->updateSequence();
	}

	/**
	 * Update sequence
	 *
	 * @return void
	 */
	protected function updateSequence(): void
	{
		$updateTypeMethod = $this->getUpdateTypeMethod();
		$this->{$updateTypeMethod}();
	}

	/**
	 * Get update type method
	 *
	 * @return string
	 */
	private function getUpdateTypeMethod(): string
	{
		return match($this->sortType){
			'up' => 'updateSequenceUp',
			'down' => 'updateSequenceDown',
			default  => 'updateSequenceOnThisSamePage'
		};
	}

	/**
	 * Update workflows sequence where there are on this same page
	 *
	 * @return void
	 */
	private function updateSequenceOnThisSamePage(): void
	{
		$createCommand = \App\Db::getInstance()->createCommand();
		foreach ($this->workflowsOrder as $sequence => $id) {
			$createCommand->update($this->baseTable, ['sequence' => $sequence], [$this->baseIndex => $id])->execute();
		}

	}

	/**
	 * Update workflows sequence when click on first row
	 *
	 * @return void
	 */
	private function updateSequenceUp(): void
	{
		$this->workflowsOrder = [$this->workflowsOrder[0]];
		$newSequenceNumber = ($this->entriesPerPage * ($this->pageNumber -1)) - 1;
		$this->updateDatabase( $newSequenceNumber);
	}

	/**
	 * Update workflows sequence when click on last row
	 *
	 * @return void
	 */
	private function updateSequenceDown(): void
	{
		$lastKeyOfArray = array_key_last($this->workflowsOrder);
		$this->workflowsOrder = [$this->workflowsOrder[$lastKeyOfArray]];
		$newSequenceNumber = ($this->entriesPerPage * $this->pageNumber);
		$this->updateDatabase($newSequenceNumber);
	}

	/**
	 * Update sequences
	 *
	 * @param int $newSequenceNumber
	 *
	 * @return void
	 */
	private function updateDatabase(int $newSequenceNumber) : void
	{
		$workflows = $this->getModuleWorkflows();
		$workflows = array_diff($workflows, $this->workflowsOrder);
		array_splice($workflows, $newSequenceNumber, 0, $this->workflowsOrder[0]);
		$createCommand = \App\Db::getInstance()->createCommand();
		$sequence = 0;
		foreach ($workflows as $id) {
			$createCommand->update($this->baseTable, ['sequence' => $sequence++], [$this->baseIndex => $id])->execute();
		}

	}

	/**
	 * Get workflows for module
	 *
	 * @return array
	 */
	private function getModuleWorkflows(): array
	{
		$workflowSequence = [];
		$workflowModuleEntries = (new App\Db\Query())->select([$this->baseIndex])->from($this->baseTable)->where(['module_name' => $this->moduleName])->orderBy(['sequence'  => SORT_ASC])->column();
		foreach ($workflowModuleEntries as $workflowId) {
			$workflowSequence[] = $workflowId;
		}
		return $workflowSequence;
	}
}
