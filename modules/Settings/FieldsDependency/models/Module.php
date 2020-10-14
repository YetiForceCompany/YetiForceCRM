<?php
/**
 * Settings fields dependency module model file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings fields dependency module model class.
 */
class Settings_FieldsDependency_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $baseTable = 's_#__fields_dependency';

	/** {@inheritdoc} */
	public $baseIndex = 'id';

	/** {@inheritdoc} */
	public $listFields = [
		'name' => 'LBL_NAME',
		'tabid' => 'LBL_SOURCE_MODULE',
		'status' => 'FL_ACTIVE',
		'views' => 'LBL_VIEWS',
		'gui' => 'LBL_GUI',
		'mandatory' => 'LBL_MANDATORY',
		'fields' => 'LBL_FIELDS',
	];

	/** {@inheritdoc} */
	public $name = 'FieldsDependency';

	/** {@inheritdoc} */
	public function getDefaultUrl()
	{
		return 'index.php?parent=Settings&module=FieldsDependency&view=List';
	}

	/** {@inheritdoc} */
	public function getCreateRecordUrl()
	{
		return 'index.php?parent=Settings&module=FieldsDependency&view=Edit';
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
		return 'index.php?parent=Settings&module=FieldsDependency&view=Edit&record=' . $recordId;
	}
}
