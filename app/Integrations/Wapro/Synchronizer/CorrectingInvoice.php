<?php

/**
 * WAPRO ERP correcting invoice synchronizer file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Wapro\Synchronizer;

/**
 * WAPRO ERP correcting invoice synchronizer class.
 */
class CorrectingInvoice extends Invoice
{
	/** {@inheritdoc} */
	const NAME = 'LBL_CORRECTING_INVOICE';

	/** {@inheritdoc} */
	const SEQUENCE = 6;

	/** {@inheritdoc} */
	public function process(): int
	{
		$query = (new \App\Db\Query())->select([
			'ID_DOKUMENTU_HANDLOWEGO', 'ID_FIRMY', 'ID_KONTRAHENTA',  'ID_DOK_ORYGINALNEGO',
			'NUMER', 'FORMA_PLATNOSCI', 'UWAGI', 'KONTRAHENT_NAZWA', 'WARTOSC_NETTO', 'WARTOSC_BRUTTO', 'DOK_KOREKTY',
			'issueTime' => 'cast (dbo.DOKUMENT_HANDLOWY.DATA_WYSTAWIENIA - 36163 as datetime)',
			'saleDate' => 'cast (dbo.DOKUMENT_HANDLOWY.DATA_SPRZEDAZY - 36163 as datetime)',
			'paymentDate' => 'cast (dbo.DOKUMENT_HANDLOWY.TERMIN_PLAT - 36163 as datetime)',
		])->from('dbo.DOKUMENT_HANDLOWY')
			->where(['ID_TYPU' => 3]);
		$pauser = \App\Pauser::getInstance('WaproCorrectingInvoiceLastId');
		if ($val = $pauser->getValue()) {
			$query->andWhere(['>', 'ID_DOKUMENTU_HANDLOWEGO', $val]);
		}
		$lastId = $s = $e = $i = $u = 0;
		foreach ($query->batch(100, $this->controller->getDb()) as $rows) {
			$lastId = 0;
			foreach ($rows as $row) {
				$this->waproId = $row['ID_DOKUMENTU_HANDLOWEGO'];
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
		if ($id = $this->findInMapTable($this->waproId, 'DOKUMENT_HANDLOWY')) {
			$this->recordModel = \Vtiger_Record_Model::getInstanceById($id, 'FCorectingInvoice');
		} else {
			$this->recordModel = \Vtiger_Record_Model::getCleanInstance('FCorectingInvoice');
			$this->recordModel->setDataForSave([\App\Integrations\Wapro::RECORDS_MAP_TABLE_NAME => [
				'wtable' => 'DOKUMENT_HANDLOWY',
			]]);
		}
		$this->recordModel->set('wapro_id', $this->waproId);
		$this->recordModel->set('finvoiceid', $this->findRelationship($this->row['ID_DOK_ORYGINALNEGO'], ['tableName' => 'DOKUMENT_HANDLOWY']));
		$this->recordModel->set($this->recordModel->getModule()->getSequenceNumberFieldName(), $this->row['NUMER']);
		$this->loadFromFieldMap();
		$this->loadDeliveryAddress('a');
		$this->loadInventory();
		if ($this->skip) {
			return 0;
		}
		$this->recordModel->save();
		\App\Cache::save('WaproMapTable', "{$this->waproId}|DOKUMENT_HANDLOWY", $this->recordModel->getId());
		if ($id) {
			return $this->recordModel->getPreviousValue() ? 1 : 3;
		}
		return 2;
	}

	/** {@inheritdoc} */
	public function getCounter(): int
	{
		return (new \App\Db\Query())->from('dbo.DOKUMENT_HANDLOWY')->where(['ID_TYPU' => 3])->count('*', $this->controller->getDb());
	}
}
