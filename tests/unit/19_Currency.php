<?php
/**
 * Currency test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class Currency extends TestCase
{

	/**
	 * Inventory id
	 */
	static $id;

	/**
	 * Save to database
	 * @return string
	 */
	public function save()
	{
		$record = null;
		if (empty($record)) {
			$recordModel = Settings_Currency_Record_Model::getInstance('Bahrain');
			if (empty($recordModel)) {
				$recordModel = new Settings_Currency_Record_Model();
			}
		} else {
			$recordModel = Settings_Currency_Record_Model::getInstance($record);
		}
		$recordModel->set('currency_name', 'Bahrain');
		$recordModel->set('conversion_rate', 1.65);
		$recordModel->set('currency_status', 'Active');
		$recordModel->set('currency_code', 'BHD');
		$recordModel->set('currency_symbol', 'BD');
		return $recordModel->save();
	}

	/**
	 * Testing currency creation
	 */
	public function testAddCurrency()
	{
		$currencyName = 'Bahrain';
		$conversionRate = 1.65;
		$currencyStatus = 'Active';
		$currencyCode = 'BHD';
		$currencySymbol = 'BD';
		static::$id = $this->save($currencyName, $conversionRate, $currencyStatus, $currencyCode, $currencySymbol);
		$this->assertNotNull(static::$id, 'Id is null');

		$row = (new \App\Db\Query())->from('vtiger_currency_info')->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['currency_name'], $currencyName);
		$this->assertEquals($row['conversion_rate'], $conversionRate);
		$this->assertEquals($row['currency_status'], $currencyStatus);
		$this->assertEquals($row['currency_code'], $currencyCode);
		$this->assertEquals($row['currency_symbol'], $currencySymbol);
	}
}
