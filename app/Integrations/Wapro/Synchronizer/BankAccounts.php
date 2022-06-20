<?php

/**
 * WAPRO ERP company bank accounts synchronizer file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Wapro\Synchronizer;

/**
 * WAPRO ERP company bank accounts synchronizer class.
 */
class BankAccounts extends \App\Integrations\Wapro\Synchronizer
{
	/** {@inheritdoc} */
	const NAME = 'LBL_COMPANY_BANK_ACCOUNTS';

	/** @var string[] Map of fields integrating with WAPRO ERP */
	const FIELDS_MAP = [
		'NAZWA' => 'name',
		'NUMER_RACHUNKU' => 'account_number',
		'bankName' => 'bank_name',
		'SWIFT' => 'swift',
	];

	/** {@inheritdoc} */
	public function process(): void
	{
		$dataReader = (new \App\Db\Query())->select(['dbo.RACHUNEK_FIRMY.*', 'dbo.BANKI.SWIFT', 'bankName' => 'dbo.BANKI.NAZWA'])->from('dbo.RACHUNEK_FIRMY')
			->leftJoin('dbo.BANKI', 'dbo.RACHUNEK_FIRMY.ID_BANKU = dbo.BANKI.ID_BANKU')
			->createCommand($this->controller->getDb())->query();
		$e = $s = $i = $u = 0;
		while ($row = $dataReader->read()) {
			try {
				switch ($this->importRecord($row)) {
					case 0:
						++$i;
						break;
					case 1:
						++$u;
						break;
					default:
						++$s;
						break;
				}
			} catch (\Throwable $th) {
				$this->log('BankAccounts', $th);
				\App\Log::error('Error during import BankAccounts: ' . PHP_EOL . $th->__toString() . PHP_EOL, 'Integrations/Wapro');
				++$e;
			}
		}
		$this->log("BankAccounts: Create {$i} | Update {$u} | Skipped {$s} | Error {$e}");
	}

	/**
	 * Import record.
	 *
	 * @param array $row
	 *
	 * @return int|null
	 */
	public function importRecord(array $row): ?int
	{
		$multiCompanyId = $this->findInMapTable($row['ID_FIRMY'], 'FIRMA');
		if (!$multiCompanyId) {
			return null;
		}
		if ($id = $this->findInMapTable($row['ID_RACHUNKU'], 'RACHUNEK_FIRMY')) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'BankAccounts');
		} else {
			$recordModel = \Vtiger_Record_Model::getCleanInstance('BankAccounts');
			$recordModel->setDataForSave([\App\Integrations\Wapro::RECORDS_MAP_TABLE_NAME => [
				'wtable' => 'RACHUNEK_FIRMY',
			]]);
		}
		$recordModel->set('bankaccount_status', $row['AKTYWNY'] ? 'PLL_ACTIVE' : 'PLL_INACTIVE');
		$recordModel->set('wapro_id', $row['ID_RACHUNKU']);
		$currencyId = \App\Fields\Currency::getIdByCode($row['SYM_WALUTY']);
		if (empty($currencyId)) {
			$currencyId = \App\Fields\Currency::addCurrency($row['SYM_WALUTY']);
		}
		$recordModel->set('currency_id', $currencyId ?? 0);
		$recordModel->set('multicompanyid', $multiCompanyId);
		foreach (self::FIELDS_MAP as $wapro => $crm) {
			$recordModel->set($crm, $row[$wapro]);
		}
		$recordModel->save();
		return $id ? 1 : 0;
	}
}
