<?php

class Settings_Workflows_UpdateSequence_Helper
{
		/**
	 * Base table name.
	 *
	 * @var string
	 */
	public $baseTable = 'com_vtiger_workflows';

	/**
	 * Base table index column name.
	 *
	 * @var string
	 */
	public $baseIndex = 'workflow_id';
	private $sortType = false;
	private $pageNumber = 0;
	private $entriesPerPage = 0;
	private $workflowsOrder = [];

	public function __construct($workflowsOrder, $pageNumber, $sortType)
	{
		$this->workflowsOrder = $workflowsOrder;
		$this->pageNumber = $pageNumber;
		$this->sortType = $sortType;
		$this->entriesPerPage = \App\Config::main('list_max_entries_per_page');
		$this->updateSequence();
	}

	protected function updateSequence()
	{
		$updateTypeMethod = $this->getUpdateTypeMethod();
		$this->{$updateTypeMethod}();
	}

	private function getUpdateTypeMethod()
	{
		return match($this->sortType){
			'up' => 'updateSequenceUp',
			'down' => 'updateSequenceDown',
			default  => 'updateSequenceOnThisSamePage'
		};
	}

	private function updateSequenceOnThisSamePage(){
		$createCommand = \App\Db::getInstance()->createCommand();
		foreach ($this->workflowsOrder as $sequence => $id) {
			$createCommand->update($this->baseTable, ['sequence' => $sequence], [$this->baseIndex => $id])->execute();
		}

	}

	private function updateSequenceUp()
	{
		$this->workflowsOrder = [$this->workflowsOrder[0]];
		$newSequenceNumber = ($this->entriesPerPage * ($this->pageNumber -1)) - 1;
		$this->updateDatabase( $newSequenceNumber);
	}

	private function updateSequenceDown(){
		$lastKeyOfArray = array_key_last($this->workflowsOrder);
		$this->workflowsOrder = [$this->workflowsOrder[$lastKeyOfArray]];
		$newSequenceNumber = ($this->entriesPerPage * $this->pageNumber);
		$this->updateDatabase($newSequenceNumber);
	}

	private function updateDatabase(int $newSequenceNumber){
		$workflows = $this->getModuleWorkflows();
		$workflows = array_diff($workflows, $this->workflowsOrder);
		array_splice($workflows, $newSequenceNumber, 0, $this->workflowsOrder[0]);
		$createCommand = \App\Db::getInstance()->createCommand();
		$sequence = 0;
		foreach ($workflows as $id) {
			$createCommand->update($this->baseTable, ['sequence' => $sequence++], [$this->baseIndex => $id])->execute();
		}

	}

	private function getModuleWorkflows(): array
	{
		$moduleName = 'Contacts';
		$workflowSequence = [];
		$workflowModuleEntries = (new App\Db\Query())->select([$this->baseIndex])->from($this->baseTable)->where(['module_name' => $moduleName])->orderBy(['sequence'  => SORT_ASC])->column();
		foreach ($workflowModuleEntries as $workflowId){
			$workflowSequence[] = $workflowId;
		}
		return $workflowSequence;

	}
}
