<?php

/**
 * ServiceContracts CRMEntity class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts extends CRMEntity
{
	public $table_name = 'vtiger_servicecontracts';
	public $table_index = 'servicecontractsid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_servicecontractscf', 'servicecontractsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_servicecontracts', 'vtiger_servicecontractscf', 'vtiger_entity_stats'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_servicecontracts' => 'servicecontractsid',
		'vtiger_servicecontractscf' => 'servicecontractsid',
		'vtiger_entity_stats' => 'crmid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Subject' => 'subject',
		'Assigned To' => 'assigned_user_id',
		'Related To' => 'sc_related_to',
		'Status' => 'contract_status',
		'Used Units' => 'used_units',
		'Total Units' => 'total_units',
		'Contract No' => 'contract_no',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Subject' => ['servicecontracts', 'subject'],
		'Status' => ['servicecontracts', 'contract_status'],
		'Due Date' => ['servicecontracts', 'due_date'],
		'Start Date' => ['servicecontracts', 'start_date'],
		'Type' => ['servicecontracts', 'contract_type'],
		'Related to' => ['servicecontracts', 'sc_related_to'],
		'Used Units' => ['servicecontracts', 'used_units'],
		'Total Units' => ['servicecontracts', 'total_units'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'Contract No' => ['servicecontracts', 'contract_no'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['subject'];
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['subject', 'assigned_user_id'];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			$moduleInstance = vtlib\Module::getInstance($moduleName);

			$accModuleInstance = vtlib\Module::getInstance('Accounts');
			$accModuleInstance->setRelatedList($moduleInstance, 'Service Contracts', ['add'], 'getDependentsList');

			$conModuleInstance = vtlib\Module::getInstance('Contacts');
			$conModuleInstance->setRelatedList($moduleInstance, 'Service Contracts', ['add'], 'getDependentsList');

			$helpDeskInstance = vtlib\Module::getInstance('HelpDesk');
			$helpDeskInstance->setRelatedList($moduleInstance, 'Service Contracts', ['ADD', 'SELECT']);

			// Initialize module sequence for the module
			$dbCommand = \App\Db::getInstance()->createCommand();
			// Make the picklist value 'Complete' for status as non-editable
			$dbCommand->update('vtiger_contract_status', ['presence' => 0], ['contract_status' => 'Complete'])->execute();
			// Mark the module as Standard module
			$dbCommand->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
		} elseif ('module.disabled' === $eventType) {
			App\EventHandler::setInActive('ServiceContracts_ServiceContractsHandler_Handler');
		} elseif ('module.enabled' === $eventType) {
			App\EventHandler::setActive('ServiceContracts_ServiceContractsHandler_Handler');
		}
	}

	/**
	 * Function to Update the parent_id of HelpDesk with sc_related_to of ServiceContracts if the parent_id is not set.
	 *
	 * @param int $focusId
	 * @param int $entityIds
	 */
	public function updateHelpDeskRelatedTo($focusId, $entityIds)
	{
		$dataReader = (new \App\Db\Query())->select(['ticketid'])->from('vtiger_troubletickets')
			->where(['and', ['or', ['parent_id' => null], ['parent_id' => 0]], ['ticketid' => $entityIds]])
			->createCommand()->query();
		while ($ticketId = $dataReader->readColumn(0)) {
			$serviceContractsInfo = (new \App\Db\Query())->select(['vtiger_crmentity.setype', 'vtiger_servicecontracts.sc_related_to'])
				->from('vtiger_servicecontracts')
				->leftJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_servicecontracts.sc_related_to')
				->where(['vtiger_servicecontracts.servicecontractsid' => $focusId])->one();
			if ('Accounts' === $serviceContractsInfo['setype']) {
				App\Db::getInstance()->createCommand()->update('vtiger_troubletickets', ['parent_id' => $serviceContractsInfo['sc_related_to']], ['ticketid' => $ticketId])->execute();
			}
		}
	}

	// Function to Compute and Update the Used Units and Progress of the Service Contract based on all the related Trouble tickets.
	public function updateServiceContractState($focusId)
	{
		$this->id = $focusId;
		$this->retrieveEntityInfo($focusId, 'ServiceContracts');
		$dataReader = (new App\Db\Query())->select(['relcrmid'])
			->from('vtiger_crmentityrel')
			->where(['module' => 'ServiceContracts', 'relmodule' => 'HelpDesk', 'crmid' => $focusId])
			->union((new App\Db\Query())->select(['crmid'])
			->from('vtiger_crmentityrel')
			->where(['relmodule' => 'ServiceContracts', 'module' => 'HelpDesk', 'relcrmid' => $focusId]))
			->createCommand()->query();
		$totalUsedUnits = 0;
		$ticketFocus = CRMEntity::getInstance('HelpDesk');
		while ($ticketId = $dataReader->readColumn(0)) {
			$ticketFocus->id = $ticketId;
			if (\App\Record::isExists($ticketId)) {
				$ticketFocus->retrieveEntityInfo($ticketId, 'HelpDesk');
				if ('closed' === strtolower($ticketFocus->column_fields['ticketstatus'])) {
					$totalUsedUnits += $this->computeUsedUnits($ticketFocus->column_fields);
				}
			}
		}
		$dataReader->close();
		$this->updateUsedUnits($totalUsedUnits);
		$this->calculateProgress();
	}

	// Function to Upate the Used Units of the Service Contract based on the given Ticket id.
	public function computeUsedUnits($ticketData, $operator = '+')
	{
		$trackingUnit = strtolower($this->column_fields['tracking_unit']);
		$workingHoursPerDay = 24;

		$usedUnits = 0;
		if ('incidents' == $trackingUnit) {
			$usedUnits = 1;
		} elseif ('days' == $trackingUnit) {
			if (!empty($ticketData['days'])) {
				$usedUnits = $ticketData['days'];
			} elseif (!empty($ticketData['hours'])) {
				$usedUnits = $ticketData['hours'] / $workingHoursPerDay;
			}
		} elseif ('hours' == $trackingUnit) {
			if (!empty($ticketData['hours'])) {
				$usedUnits = $ticketData['hours'];
			} elseif (!empty($ticketData['days'])) {
				$usedUnits = $ticketData['days'] * $workingHoursPerDay;
			}
		}

		return $usedUnits;
	}

	/**
	 * Function to Upate the Used Units of the Service Contract.
	 *
	 * @param float $usedUnits
	 */
	public function updateUsedUnits($usedUnits)
	{
		$this->column_fields['used_units'] = $usedUnits;
		\App\Db::getInstance()->createCommand()->update($this->table_name, ['used_units' => $usedUnits], ['servicecontractsid' => $this->id])->execute();
	}

	/**
	 * Function to Calculate the End Date, Planned Duration, Actual Duration and Progress of a Service Contract.
	 */
	public function calculateProgress()
	{
		$db = \App\Db::getInstance();
		$params = [];

		$startDate = $this->column_fields['start_date'];
		$dueDate = $this->column_fields['due_date'];
		$endDate = $this->column_fields['end_date'];

		$usedUnits = \vtlib\Functions::formatDecimal($this->column_fields['used_units']);
		$totalUnits = \vtlib\Functions::formatDecimal($this->column_fields['total_units']);

		$contractStatus = $this->column_fields['contract_status'];

		// Update the End date if the status is Complete or if the Used Units reaches/exceeds Total Units
		// We need to do this first to make sure Actual duration is computed properly
		if ('Complete' === $contractStatus || (!empty($usedUnits) && !empty($totalUnits) && $usedUnits >= $totalUnits)) {
			if (empty($endDate)) {
				$endDate = date('Y-m-d');
				$db->createCommand()->update($this->table_name, ['end_date' => $endDate], ['servicecontractsid' => $this->id])->execute();
			}
		} else {
			$endDate = null;
			$db->createCommand()->update($this->table_name, ['end_date' => $endDate], ['servicecontractsid' => $this->id])->execute();
		}

		// Calculate the Planned Duration based on Due date and Start date. (in days)
		if (!empty($dueDate) && !empty($startDate)) {
			$params['planned_duration'] = \App\Fields\DateTime::getDiff($startDate, $dueDate, 'days');
		} else {
			$params['planned_duration'] = '';
		}

		// Calculate the Actual Duration based on End date and Start date. (in days)
		if (!empty($endDate) && !empty($startDate)) {
			$params['actual_duration'] = \App\Fields\DateTime::getDiff($startDate, $endDate, 'days');
		} else {
			$params['actual_duration'] = '';
		}
		// Update the Progress based on Used Units and Total Units (in percentage)
		if (!empty($usedUnits) && !empty($totalUnits)) {
			$params['progress'] = (float) (($usedUnits * 100) / $totalUnits);
		} else {
			$params['progress'] = null;
		}
		$db->createCommand()->update($this->table_name, $params, ['servicecontractsid' => $this->id])->execute();
	}
}
