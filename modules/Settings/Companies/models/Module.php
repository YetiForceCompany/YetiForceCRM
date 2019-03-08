<?php

/**
 * Companies module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 's_yf_companies';
	public $baseIndex = 'id';
	public $listFields = ['name' => 'LBL_NAME', 'status' => 'LBL_STATUS', 'type' => 'LBL_TYPE', 'email' => 'LBL_EMAIL', 'city' => 'LBL_CITY', 'country' => 'LBL_COUNTRY', 'website' => 'LBL_WEBSITE'];
	/**
	 * List of fields in form.
	 *
	 * @var array
	 */
	public static $formFields = [
		'name' => [
			'label' => 'LBL_NAME',
			'registerView' => true
		],
		'type' => [
			'label' => 'LBL_TYPE',
			'registerView' => true
		],
		'industry' => [
			'label' => 'LBL_INDUSTRY',
			'registerView' => true
		],
		'city' => [
			'label' => 'LBL_CITY',
			'registerView' => true
		],
		'country' => [
			'label' => 'LBL_COUNTRY',
			'registerView' => true
		],
		'companysize' => [
			'label' => 'LBL_COMPANYSIZE',
			'registerView' => true
		],
		'website' => [
			'label' => 'LBL_WEBSITE',
			'registerView' => true
		],
		'spacer' => [
			'label' => '',
			'registerView' => true
		],
		'newsletter' => [
			'label' => 'LBL_YETIFORCE_NEWSLETTER',
			'registerView' => true
		],
		'firstname' => [
			'label' => 'LBL_FIRSTNAME',
			'registerView' => true
		],
		'lastname' => [
			'label' => 'LBL_LASTNAME',
			'registerView' => true
		],
		'email' => [
			'label' => 'LBL_EMAIL',
			'registerView' => true
		],
		'logo' => [
			'label' => 'LBL_LOGO',
			'registerView' => false
		],
	];
	public $name = 'Companies';

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=Companies&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module.
	 *
	 * @return string URL
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=Companies&parent=Settings&view=Edit';
	}

	/**
	 * Function to get the column names.
	 *
	 * @return array|false
	 */
	public static function getColumnNames()
	{
		$tableSchema = \App\Db::getInstance('admin')->getTableSchema('s_#__companies', true);
		if ($tableSchema) {
			return $tableSchema->getColumnNames();
		}
		return false;
	}

	public static function getIndustryList()
	{
		return array_merge(
			(new \App\Db\Query())->select(['industry'])->from('vtiger_industry')->column(), (new \App\Db\Query())->select(['subindustry'])->from('vtiger_subindustry')->column()
		);
	}

	/**
	 * Return list fields in form.
	 *
	 * @return string[]
	 */
	public static function getFormFields()
	{
		return static::$formFields;
	}

	/**
	 * Names of fields.
	 *
	 * @return array
	 */
	public function getNameFields()
	{
		$columnNames = self::getColumnNames();
		unset($columnNames[array_search('id', $columnNames)]);
		$editFields = array_keys(self::$formFields);
		usort($columnNames, function ($a, $b) use ($editFields) {
			return array_search($a, $editFields) < array_search($b, $editFields) ? -1 : 1;
		});
		return $columnNames;
	}
}
