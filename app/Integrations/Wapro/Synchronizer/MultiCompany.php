<?php

/**
 * WAPRO ERP multi company synchronizer file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Wapro\Synchronizer;

/**
 * WAPRO ERP multi company synchronizer class.
 */
class MultiCompany extends \App\Integrations\Wapro\Synchronizer
{
	/** {@inheritdoc} */
	const NAME = 'LBL_MULTI_COMPANY';

	/** @var string[] Map of fields integrating with WAPRO ERP */
	const FIELDS_MAP = [
		'NAZWA' => 'company_name',
		'NIP' => 'vat',
		'REGON' => 'companyid1',
		'WOJEWODZTWO' => 'addresslevel2a',
		'POWIAT' => 'addresslevel3a',
		'GMINA' => 'addresslevel4a',
		'MIEJSCOWOSC' => 'addresslevel5a',
		'POCZTA' => 'addresslevel6a',
		'KOD_POCZTOWY' => 'addresslevel7a',
		'ULICA' => 'addresslevel8a',
		'NR_DOMU' => 'buildingnumbera',
		'NR_LOKALU' => 'localnumbera',
		'SKRYTKA' => 'poboxa',
	];

	/** {@inheritdoc} */
	public function process(): void
	{
		$dataReader = (new \App\Db\Query())->from('dbo.FIRMA')
			->leftJoin('dbo.ADRESY_FIRMY', 'dbo.FIRMA.ID_ADRESU_DOMYSLNEGO = dbo.ADRESY_FIRMY.ID_ADRESY_FIRMY')
			->createCommand($this->controller->getDb())->query();
		$e = $i = $u = 0;
		while ($row = $dataReader->read()) {
			try {
				if ($this->importRecord($row)) {
					++$u;
				} else {
					++$i;
				}
			} catch (\Throwable $th) {
				$this->log('MultiCompany', $th);
				\App\Log::error('Error during import MultiCompany: ' . PHP_EOL . $th->__toString() . PHP_EOL, 'Integrations/Wapro');
				++$e;
			}
		}
		$this->log("MultiCompany: Create {$i} | Update {$u} | Error {$e}");
	}

	/**
	 * Import record.
	 *
	 * @param array $row
	 *
	 * @return int
	 */
	public function importRecord(array $row): int
	{
		if ($id = $this->findInMapTable($row['ID_FIRMY'], 'FIRMA')) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'MultiCompany');
		} else {
			$recordModel = \Vtiger_Record_Model::getCleanInstance('MultiCompany');
			$recordModel->setDataForSave([\App\Integrations\Wapro::RECORDS_MAP_TABLE_NAME => [
				'wtable' => 'FIRMA',
			]]);
		}
		$recordModel->set('wapro_id', $row['ID_FIRMY']);
		$recordModel->set('addresslevel1a', \App\Fields\Country::getCountryName($row['SYM_KRAJU']));
		foreach (self::FIELDS_MAP as $wapro => $crm) {
			$recordModel->set($crm, $row[$wapro]);
		}
		$recordModel->save();
		return $id ? 1 : 0;
	}
}
