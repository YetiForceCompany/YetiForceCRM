<?php

/**
 * WAPRO ERP accounts synchronizer file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Wapro\Synchronizer;

/**
 * WAPRO ERP accounts synchronizer class.
 */
class Accounts extends \App\Integrations\Wapro\Synchronizer
{
	/** {@inheritdoc} */
	const NAME = 'LBL_ACCOUNTS';

	/** {@inheritdoc} */
	const SEQUENCE = 2;

	/** {@inheritdoc} */
	protected $fieldMap = [
		'ID_FIRMY' => ['fieldName' => 'multiCompanyId', 'fn' => 'findRelationship', 'tableName' => 'FIRMA', 'skipMode' => true],
		'NAZWA' => ['fieldName' => 'accountname', 'fn' => 'decode'],
		'NIP' => 'vat_id',
		'REGON' => 'registration_number_2',
		'UWAGI' => 'description',
		'ADRES_WWW' => 'website',
		'DOMYSLNY_RABAT' => ['fieldName' => 'discount', 'fn' => 'convertDiscount'],
		'ADRES_EMAIL' => 'email1',
		'TELEFON_FIRMOWY' => ['fieldName' => 'phone', 'fn' => 'convertPhone'],
		'SYM_KRAJU' => ['fieldName' => 'addresslevel1a', 'fn' => 'convertCountry'],
		'WOJEWODZTWO' => 'addresslevel2a',
		'POWIAT' => 'addresslevel3a',
		'MIEJSCOWOSC' => 'addresslevel5a',
		'KOD_POCZTOWY' => 'addresslevel7a',
		'ULICA_LOKAL' => 'addresslevel8a',
		'SYM_KRAJU_KOR' => ['fieldName' => 'addresslevel1b', 'fn' => 'convertCountry'],
		'WOJEWODZTWO_KOR' => 'addresslevel2b',
		'POWIAT_KOR' => 'addresslevel3b',
		'MIEJSCOWOSC_KOR' => 'addresslevel5b',
		'KOD_POCZTOWY_KOR' => 'addresslevel7b',
		'ULICA_LOKAL_KOR' => 'addresslevel8b',
	];

	/** {@inheritdoc} */
	public function process(): int
	{
		$query = (new \App\Db\Query())->from('dbo.KONTRAHENT');
		$pauser = \App\Pauser::getInstance('WaproAccountLastId');
		if ($val = $pauser->getValue()) {
			$query->where(['>', 'ID_KONTRAHENTA', $val]);
		}
		$lastId = $s = $e = $i = $u = 0;
		foreach ($query->batch(100, $this->controller->getDb()) as $rows) {
			$lastId = 0;
			foreach ($rows as $row) {
				$this->waproId = $row['ID_KONTRAHENTA'];
				$this->row = $row;
				$this->skip = false;
				try {
					switch ($this->importRecord()) {
						default:
						case 0:
							++$s;
							break;
						case 1:
							++$u;
							break;
						case 2:
							++$i;
							break;
					}
					$lastId = $this->waproId;
				} catch (\Throwable $th) {
					$this->logError($th);
					++$e;
				}
			}
			$pauser->setValue($lastId);
			if ($this->controller->cron && $this->controller->cron->checkTimeout()) {
				break;
			}
		}
		if (0 == $lastId) {
			$pauser->destroy();
		}
		$this->log("Create {$i} | Update {$u} | Skipped {$s} | Error {$e}");
		return $i + $u;
	}

	/** {@inheritdoc} */
	public function importRecord(): int
	{
		if ($id = $this->findInMapTable($this->waproId, 'KONTRAHENT')) {
			$this->recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Accounts');
		} else {
			$this->recordModel = \Vtiger_Record_Model::getCleanInstance('Accounts');
			$this->recordModel->setDataForSave([\App\Integrations\Wapro::RECORDS_MAP_TABLE_NAME => [
				'wtable' => 'KONTRAHENT',
			]]);
		}
		$this->recordModel->set('wapro_id', $this->waproId);
		$this->loadFromFieldMap();
		if ($this->skip) {
			return 0;
		}
		$this->recordModel->save();
		\App\Cache::save('WaproMapTable', "{$this->waproId}|KONTRAHENT", $this->recordModel->getId());
		if ($id) {
			return $this->recordModel->getPreviousValue() ? 1 : 3;
		}
		return 2;
	}

	/**
	 * Convert discount to system format.
	 *
	 * @param string $value
	 * @param array  $params
	 *
	 * @return int
	 */
	protected function convertDiscount(string $value, array $params): float
	{
		return -((float) $value);
	}

	/** {@inheritdoc} */
	public function getCounter(): int
	{
		return (new \App\Db\Query())->from('dbo.KONTRAHENT')->count('*', $this->controller->getDb());
	}
}
