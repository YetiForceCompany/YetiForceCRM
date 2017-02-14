<?php

/**
 * Companies module model class
 * @package YetiForce.Settings.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_Module_Model extends Settings_Vtiger_Module_Model
{

	public $baseTable = 's_yf_companies';
	public $baseIndex = 'id';
	public $listFields = ['name' => 'LBL_NAME', 'phone' => 'LBL_PHONE', 'vatid' => 'LBL_VATID', 'email' => 'LBL_EMAIL', 'city' => 'LBL_CITY'];
	public $name = 'Companies';

	/**
	 * Function to get the url for default view of the module
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=Companies&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module
	 * @return string URL
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=Companies&parent=Settings&view=Edit';
	}

	/**
	 * Function to get the column names
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
			(new \App\Db\Query())->select(['industry'])->from('vtiger_industry')->column()
			, (new \App\Db\Query())->select(['subindustry'])->from('vtiger_subindustry')->column()
		);
	}

	/**
	 * Function to get the all companies
	 * @return array
	 */
	public static function getAllCompanies()
	{
		$db = App\Db::getInstance('admin');
		$query = new \App\Db\Query();
		$query->select(['id', 'name', 'default'])->from('s_#__companies');
		return $query->createCommand()->queryAllByGroup(1);
	}
}
