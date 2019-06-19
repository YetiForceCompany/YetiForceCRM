<?php
/**
 * Settings SlaPolicy module model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SlaPolicy_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Base table.
	 *
	 * @var string
	 */
	public $baseTable = 's_#__sla_policy';
	/**
	 * Base index.
	 *
	 * @var string
	 */
	public $baseIndex = 'id';
	/**
	 * List fields.
	 *
	 * @var array
	 */
	public $listFields = [
		'name' => 'LBL_NAME',
		'tabid' => 'LBL_SOURCE_MODULE',
		'operational_hours' => 'LBL_OPERATIONAL_HOURS',
		'reaction_time' => 'LBL_REACTION_TIME',
		'idle_time' => 'LBL_IDLE_TIME',
		'resolve_time' => 'LBL_RESOLVE_TIME',
		'business_hours' => 'LBL_BUSINESS_HOURS'
	];
	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $name = 'SlaPolicy';

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=SlaPolicy&parent=Settings&view=List';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=SlaPolicy&parent=Settings&view=Edit';
	}

	/**
	 * Get edit record url.
	 *
	 * @param int $recordId
	 *
	 * @return string
	 */
	public function getEditRecordUrl(int $recordId)
	{
		return 'index.php?module=SlaPolicy&parent=Settings&view=Edit&record=' . $recordId;
	}

	/**
	 * Get modules name related to ServiceContracts.
	 *
	 * @return string[]
	 */
	public static function getModules(): array
	{
		$modules = [];
		foreach (\App\Field::getRelatedFieldForModule(false, 'ServiceContracts') as $moduleName => $value) {
			if (App\RecordStatus::getFieldName($moduleName)) {
				$modules[] = $moduleName;
			}
		}
		return $modules;
	}
}
