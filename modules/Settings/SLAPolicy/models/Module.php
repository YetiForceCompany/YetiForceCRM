<?php
/**
 * Settings SLAPolicy module model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SLAPolicy_Module_Model extends Settings_Vtiger_Module_Model
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
	public $listFields = ['name' => 'LBL_NAME', 'operational_hours' => 'LBL_OPERATIONAL_HOURS', 'tabid' => 'LBL_SOURCE_MODULE'];
	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $name = 'SLAPolicy';

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=SLAPolicy&parent=Settings&view=List';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=SLAPolicy&parent=Settings&view=Edit';
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
		return 'index.php?module=SLAPolicy&parent=Settings&view=Edit&record=' . $recordId;
	}
}
