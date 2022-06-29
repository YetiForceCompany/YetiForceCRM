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

	/** {@inheritdoc} */
	protected $fieldMap = [
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
		'SYM_KRAJU' => ['fieldName' => 'addresslevel1a', 'fn' => 'convertCountry'],
	];

	/** {@inheritdoc} */
	public function process(): void
	{
		$dataReader = (new \App\Db\Query())->from('dbo.FIRMA')
			->leftJoin('dbo.ADRESY_FIRMY', 'dbo.FIRMA.ID_ADRESU_DOMYSLNEGO = dbo.ADRESY_FIRMY.ID_ADRESY_FIRMY')
			->createCommand($this->controller->getDb())->query();
		$s = $e = $i = $u = 0;
		while ($row = $dataReader->read()) {
			$this->waproId = $row['ID_FIRMY'];
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
			} catch (\Throwable $th) {
				$this->logError($th);
				++$e;
			}
		}
		$this->log("Create {$i} | Update {$u} | Skipped {$s} | Error {$e}");
	}

	/** {@inheritdoc} */
	public function importRecord(): int
	{
		if ($id = $this->findInMapTable($this->waproId, 'FIRMA')) {
			$this->recordModel = \Vtiger_Record_Model::getInstanceById($id, 'MultiCompany');
		} else {
			$this->recordModel = \Vtiger_Record_Model::getCleanInstance('MultiCompany');
			$this->recordModel->setDataForSave([\App\Integrations\Wapro::RECORDS_MAP_TABLE_NAME => [
				'wtable' => 'FIRMA',
			]]);
		}
		$this->recordModel->set('wapro_id', $this->waproId);
		$this->loadFromFieldMap();
		$this->recordModel->save();
		return $id ? 1 : 2;
	}
}
