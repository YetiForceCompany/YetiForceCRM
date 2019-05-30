<?php
/**
 * Settings_BusinessHours_Module_Model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_BusinessHours_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 's_#__businesshours';
	public $baseIndex = 'businesshoursid';
	public $listFields = [
		'businesshoursname' => 'LBL_NAME',
		'working_days' => 'LBL_WORKING_DAYS',
		'working_hours_from' => 'LBL_WORKING_HOURS_FROM',
		'working_hours_to' => 'LBL_WORKING_HOURS_TO',
		'holidays' => 'LBL_HOLIDAYS',
		'default' => 'LBL_DEFAULT'
	];
	public $nameFields = ['businesshoursname'];
	public $name = 'BusinessHours';

	/**
	 * {@inheritdoc}
	 */
	public function getIndexViewUrl(): string
	{
		return 'index.php?module=' . $this->getName() . '&parent=' . $this->getParentName() . '&view=List';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultUrl(): string
	{
		return 'index.php?module=BusinessHours&parent=Settings&view=List';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListViewUrl(): string
	{
		return 'index.php?module=BusinessHours&parent=Settings&view=List';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewUrl(): string
	{
		return 'index.php?module=BusinessHours&parent=Settings&view=Edit';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCreateRecordUrl(): string
	{
		return 'index.php?module=BusinessHours&parent=Settings&view=Edit';
	}

	/**
	 * {@inheritdoc}
	 */
	public function isPagingSupported(): bool
	{
		return false;
	}
}
