<?php
/**
 * Settings WAPRO ERP module model file.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings WAPRO ERP module model class.
 */
class Settings_Wapro_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'Wapro';

	/** {@inheritdoc} */
	public $baseTable = \App\Integrations\Wapro::TABLE_NAME;

	/** {@inheritdoc} */
	public $baseIndex = 'id';

	/** {@inheritdoc} */
	public $listFields = [
		'name' => 'FL_NAME',
		'status' => 'FL_STATUS',
		'server' => 'FL_SERVER',
		'database' => 'FL_DATABASE',
		'username' => 'FL_USERNAME',
	];

	/** {@inheritdoc} */
	public function getDefaultUrl()
	{
		return "index.php?parent=Settings&module={$this->getName()}&view=List";
	}

	/** {@inheritdoc} */
	public function getCreateRecordUrl()
	{
		return "index.php?parent=Settings&module={$this->getName()}&view=Edit";
	}

	/** @var array Field form array. */
	public static $formFields = [
		'name' => ['required' => 1, 'default' => '', 'purifyType' => \APP\Purifier::TEXT, 'label' => 'FL_NAME', 'maximumlength' => 255],
		'status' => ['required' => 0, 'purifyType' => \App\Purifier::BOOL, 'label' => 'FL_STATUS', 'maximumlength' => 2],
		'server' => ['required' => 1, 'purifyType' => 'ipOrDomain', 'label' => 'FL_SERVER', 'maximumlength' => 255],
		'port' => ['required' => 0, 'purifyType' => 'port', 'label' => 'FL_PORT', 'maximumlength' => 255, 'default' => 1433],
		'database' => ['required' => 1, 'purifyType' => 'dbName', 'label' => 'FL_DATABASE', 'maximumlength' => 255],
		'username' => ['required' => 1, 'purifyType' => 'dbUserName', 'label' => 'FL_USERNAME', 'maximumlength' => 255],
		'password' => ['required' => 1, 'purifyType' => \App\Purifier::TEXT, 'label' => 'FL_PASSWORD', 'maximumlength' => 150],
	];

	/**
	 * Return list fields in form.
	 *
	 * @return array
	 */
	public function getFormFields(): array
	{
		return static::$formFields;
	}
}
