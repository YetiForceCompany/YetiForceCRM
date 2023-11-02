<?php

/**
 * Companies module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * @inheritdoc
	 */
	public $baseTable = 's_yf_companies';

	/**
	 * @inheritdoc
	 */
	public $baseIndex = 'id';

	/**
	 * @inheritdoc
	 */
	public $listFields = [
		'name' => 'LBL_NAME',
        'email' => 'LBL_EMAIL',
        'vat_id' => 'LBL_VAT_ID',
        'country' => 'LBL_COUNTRY',
        'industry' => 'LBL_INDUSTRY',
		'website' => 'LBL_WEBSITE',
	];

	/**
	 * List of fields in form.
	 *
	 * @var array
	 */
	public static array $formFields = [
		'name' => [],
        'email' => [],
		'vat_id' => [
			'infoText' => 'LBL_VAT_ID_INFO',
		],
		'country' => [],
		'industry' => [],
		'website' => [
			'infoText' => 'LBL_WEBSITE_INFO',
		],
	];

	/**
	 * @inheritdoc
	 */
	public $name = 'Companies';

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string URL
	 */
	public function getDefaultUrl(): string
	{
		return 'index.php?parent=Settings&module=Companies&view=Edit';
	}

	/**
	 * New entities mustn't be created.
	 *
	 * @return bool
	 */
	public function hasCreatePermissions(): bool
	{
		return false;
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
	public static function getFormFields(): array
	{
		return static::$formFields;
	}

	/**
	 * Names of fields.
	 *
	 * @return bool|array
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
