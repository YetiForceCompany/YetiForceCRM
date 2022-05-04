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
	private $workflowsOrder = [];

	public function __construct($workflowsOrder, $pageNumber, $sortType)
	{
		$this->workflowsOrder = $workflowsOrder;
		$this->pageNumber = $pageNumber;
		$this->sortType = $sortType;
		$this->updateSequence();
	}

	protected function updateSequence()
	{
		$updateTypeMethod = $this->getUpdateTypeMethod();
		$this->{$updateTypeMethod}();
	}

	/*
		*/

	private function getUpdateTypeMethod()
	{

			return match($this->sortType){
				false => 'updateSequenceOnThisSamePage',
				'up' => 'updateSequenceUp',
				'down' => 'updateSequenceDown'
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
		$entriesPerPage = \App\Config::main('list_max_entries_per_page');
		$newSequenceNumber = ($entriesPerPage * $this->pageNumber) - 2;
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
