<?php

/**
 * Meeting Services module model file.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Settings_MeetingServices_Module_Model class.
 */
class Settings_MeetingServices_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'MeetingServices';

	/** {@inheritdoc} */
	public $baseTable = \App\MeetingService::TABLE_NAME;

	/** {@inheritdoc} */
	public $baseIndex = 'id';

	/** {@inheritdoc} */
	public $listFields = [
		'url' => 'FL_URL',
		'status' => 'FL_STATUS',
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

	/**
	 * Field form array.
	 *
	 * @var array
	 */
	public static $formFields = [
		'url' => ['required' => 1, 'purifyType' => \App\Purifier::URL, 'label' => 'FL_URL', 'maximumlength' => '255'],
		'status' => ['required' => 0, 'purifyType' => \App\Purifier::BOOL, 'label' => 'FL_STATUS', 'maximumlength' => '2'],
		'key' => ['required' => 1, 'default' => '', 'purifyType' => \APP\Purifier::TEXT, 'label' => 'FL_APP_ID', 'maximumlength' => '64'],
		'secret' => ['required' => 1, 'default' => '', 'purifyType' => \APP\Purifier::TEXT, 'label' => 'FL_SECRET_KEY', 'maximumlength' => '100'],
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
