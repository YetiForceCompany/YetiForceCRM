<?php

/**
 * Companies module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 's_yf_companies';
	public $baseIndex = 'id';
	public $listFields = ['name' => 'LBL_NAME', 'status' => 'LBL_STATUS', 'type' => 'LBL_TYPE', 'email' => 'LBL_EMAIL', 'address' => 'AddressLevel8', 'post_code' => 'AddressLevel7', 'city' => 'LBL_CITY', 'country' => 'LBL_COUNTRY', 'website' => 'LBL_WEBSITE', 'vat_id' => 'LBL_VAT_ID'];
	/**
	 * List of fields in form.
	 *
	 * @var array
	 */
	public static $formFields = [
		'type' => [
			'registerView' => true,
			'infoText' => 'LBL_TYPE_INFO',
		],
		'name' => [
			'registerView' => true,
			'paymentData' => true,
		],
		'vat_id' => [
			'paymentData' => true,
			'registerView' => true,
			'infoText' => 'LBL_VAT_ID_INFO',
		],
		'country' => [
			'registerView' => true,
			'paymentData' => true,
		],
		'post_code' => [
			'paymentData' => true,
			'registerView' => true,
		],
		'city' => [
			'registerView' => true,
		],
		'address' => [
			'paymentData' => true,
			'registerView' => true,
		],
		'industry' => [
			'registerView' => true,
		],
		'companysize' => [
			'registerView' => true,
		],
		'website' => [
			'registerView' => true,
			'infoText' => 'LBL_WEBSITE_INFO',
		],
		'spacer' => [
			'registerView' => true,
		],
		'newsletter' => [
			'registerView' => true,
		],
		'firstname' => [
			'registerView' => true,
		],
		'lastname' => [
			'registerView' => true,
		],
		'email' => [
			'registerView' => true,
		],
		'logo' => [
			'registerView' => false,
		],
		'facebook' => [
			'brandBlock' => true,
		],
		'twitter' => [
			'brandBlock' => true,
		],
		'linkedin' => [
			'brandBlock' => true,
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

	public static function getIndustryList(): array
	{
		return array_merge(
			(new \App\Db\Query())->select(['industry'])->from('vtiger_industry')->orderBy('sortorderid')->column(),
			(new \App\Db\Query())->select(['subindustry'])->from('vtiger_subindustry')->orderBy('sortorderid')->column()
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
