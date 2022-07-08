<?php

/**
 * WAPRO ERP contacts synchronizer file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Wapro\Synchronizer;

/**
 * WAPRO ERP contacts synchronizer class.
 */
class Contacts extends \App\Integrations\Wapro\Synchronizer
{
	/** {@inheritdoc} */
	const NAME = 'LBL_CONTACTS';

	/** {@inheritdoc} */
	const SEQUENCE = 3;

	/** {@inheritdoc} */
	protected $fieldMap = [
		'ID_KONTRAHENTA' => ['fieldName' => 'parent_id', 'fn' => 'findRelationship', 'tableName' => 'KONTRAHENT'],
		'IMIE' => ['fieldName' => 'firstname', 'fn' => 'decode'],
		'NAZWISKO' => ['fieldName' => 'lastname', 'fn' => 'decode'],
		'TYTUL' => 'jobtitle',
		'TEL' => ['fieldName' => 'phone', 'fn' => 'convertPhone'],
		'TEL_KOM' => ['fieldName' => 'mobile', 'fn' => 'convertPhone'],
		'E_MAIL' => 'email',
		'E_MAIL_DW' => 'secondary_email',
		'UWAGI' => 'description',
	];

	/** {@inheritdoc} */
	public function process(): int
	{
		$query = (new \App\Db\Query())->from('dbo.KONTAKT');
		$pauser = \App\Pauser::getInstance('WaproContactsLastId');
		if ($val = $pauser->getValue()) {
			$query->where(['>', 'ID_KONTAKTU', $val]);
		}
		$lastId = $s = $e = $i = $u = 0;
		foreach ($query->batch(100, $this->controller->getDb()) as $rows) {
			$lastId = 0;
			foreach ($rows as $row) {
				$this->waproId = $row['ID_KONTAKTU'];
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
		if ($id = $this->findInMapTable($this->waproId, 'KONTAKT')) {
			$this->recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Contacts');
		} else {
			$this->recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
			$this->recordModel->setDataForSave([\App\Integrations\Wapro::RECORDS_MAP_TABLE_NAME => [
				'wtable' => 'KONTAKT',
			]]);
		}
		$this->recordModel->set('wapro_id', $this->waproId);
		$this->loadFromFieldMap();
		if ($this->skip) {
			return 0;
		}
		$this->recordModel->save();
		\App\Cache::save('WaproMapTable', "{$this->waproId}|KONTAKT", $this->recordModel->getId());
		if ($id) {
			return $this->recordModel->getPreviousValue() ? 1 : 3;
		}
		return 2;
	}

	/** {@inheritdoc} */
	public function getCounter(): int
	{
		return (new \App\Db\Query())->from('dbo.KONTAKT')->count('*', $this->controller->getDb());
	}
}
