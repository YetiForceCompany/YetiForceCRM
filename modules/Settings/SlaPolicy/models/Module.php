<?php
/**
 * Settings SlaPolicy module model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		'operational_hours' => ['LBL_OPERATIONAL_HOURS', 'ServiceContracts'],
		'reaction_time' => ['LBL_REACTION_TIME', 'ServiceContracts'],
		'idle_time' => ['LBL_IDLE_TIME', 'ServiceContracts'],
		'resolve_time' => ['LBL_RESOLVE_TIME', 'ServiceContracts'],
		'business_hours' => ['LBL_BUSINESS_HOURS', 'ServiceContracts']
	];

	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $name = 'SlaPolicy';

	/** {@inheritdoc} */
	public function getDefaultUrl()
	{
		return 'index.php?module=SlaPolicy&parent=Settings&view=List';
	}

	/** {@inheritdoc} */
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
}
