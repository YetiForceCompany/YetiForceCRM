<?php
/**
 * Settings_BusinessHours_Module_Model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_BusinessHours_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Base table.
	 *
	 * @var string
	 */
	public $baseTable = 's_#__business_hours';
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
		'working_days' => 'LBL_WORKING_DAYS',
		'working_hours_from' => 'LBL_WORKING_HOURS_FROM',
		'working_hours_to' => 'LBL_WORKING_HOURS_TO',
		'default_times' => 'LBL_DEFAULT_TIMES',
		'default' => 'LBL_DEFAULT'
	];
	/**
	 * Name fields.
	 *
	 * @var array
	 */
	public $nameFields = ['name'];
	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $name = 'BusinessHours';

	/** {@inheritdoc} */
	public function getIndexViewUrl(): string
	{
		return 'index.php?module=' . $this->getName() . '&parent=' . $this->getParentName() . '&view=List';
	}

	/** {@inheritdoc} */
	public function getDefaultUrl(): string
	{
		return 'index.php?module=BusinessHours&parent=Settings&view=List';
	}

	/** {@inheritdoc} */
	public function getListViewUrl(): string
	{
		return 'index.php?module=BusinessHours&parent=Settings&view=List';
	}

	/** {@inheritdoc} */
	public function getEditViewUrl(): string
	{
		return 'index.php?module=BusinessHours&parent=Settings&view=Edit';
	}

	/** {@inheritdoc} */
	public function getCreateRecordUrl(): string
	{
		return 'index.php?module=BusinessHours&parent=Settings&view=Edit';
	}

	/** {@inheritdoc} */
	public function isPagingSupported(): bool
	{
		return false;
	}
}
