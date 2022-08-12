<?php

/**
 * WAPRO ERP invoice synchronizer file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Wapro\Synchronizer;

/**
 * WAPRO ERP invoice synchronizer class.
 */
class Invoice extends \App\Integrations\Wapro\Synchronizer
{
	/** {@inheritdoc} */
	const NAME = 'LBL_INVOICE';

	/** {@inheritdoc} */
	const SEQUENCE = 5;

	/** @var string[] Map for payment methods with WAPRO ERP */
	const PAYMENT_METHODS_MAP = [
		'gotówka' => 'PLL_CASH',
		'przelew' => 'PLL_TRANSFER',
		'czek' => 'PLL_CHECK',
		'pobranie' => 'PLL_CASH_ON_DELIVERY',
	];

	/** {@inheritdoc} */
	protected $fieldMap = [
		'ID_FIRMY' => ['fieldName' => 'multiCompanyId', 'fn' => 'findRelationship', 'tableName' => 'FIRMA', 'skipMode' => true],
		'ID_KONTRAHENTA' => ['fieldName' => 'accountid', 'fn' => 'findRelationship', 'tableName' => 'KONTRAHENT', 'skipMode' => true],
		'FORMA_PLATNOSCI' => ['fieldName' => 'payment_methods', 'fn' => 'convertPaymentMethods'],
		'UWAGI' => 'description',
		'KONTRAHENT_NAZWA' => ['fieldName' => 'company_name_a', 'fn' => 'decode'],
		'issueTime' => ['fieldName' => 'issue_time', 'fn' => 'convertDate'],
		'saleDate' => ['fieldName' => 'saledate', 'fn' => 'convertDate'],
		'paymentDate' => ['fieldName' => 'paymentdate', 'fn' => 'convertDate'],
	];

	/** {@inheritdoc} */
	public function process(): int
	{
		$query = (new \App\Db\Query())->select([
			'ID_DOKUMENTU_HANDLOWEGO', 'ID_FIRMY', 'ID_KONTRAHENTA', 'ID_DOK_ORYGINALNEGO',
			'NUMER', 'FORMA_PLATNOSCI', 'UWAGI', 'KONTRAHENT_NAZWA', 'WARTOSC_NETTO', 'WARTOSC_BRUTTO', 'DOK_KOREKTY', 'DATA_KURS_WAL', 'DOK_WAL', 'SYM_WAL',
			'issueTime' => 'cast (dbo.DOKUMENT_HANDLOWY.DATA_WYSTAWIENIA - 36163 as datetime)',
			'saleDate' => 'cast (dbo.DOKUMENT_HANDLOWY.DATA_SPRZEDAZY - 36163 as datetime)',
			'paymentDate' => 'cast (dbo.DOKUMENT_HANDLOWY.TERMIN_PLAT - 36163 as datetime)',
			'currencyDate' => 'cast (dbo.DOKUMENT_HANDLOWY.DATA_KURS_WAL - 36163 as datetime)',
		])->from('dbo.DOKUMENT_HANDLOWY')
			->where(['ID_TYPU' => 1]);
		$pauser = \App\Pauser::getInstance('WaproInvoiceLastId');
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
			$this->recordModel = \Vtiger_Record_Model::getInstanceById($id, 'FInvoice');
		} else {
			$this->recordModel = \Vtiger_Record_Model::getCleanInstance('FInvoice');
			$this->recordModel->setDataForSave([\App\Integrations\Wapro::RECORDS_MAP_TABLE_NAME => [
				'wtable' => 'DOKUMENT_HANDLOWY',
			]]);
		}
		$this->recordModel->set('wapro_id', $this->waproId);
		$this->recordModel->set('finvoice_status', 'PLL_UNASSIGNED');
		$this->recordModel->set('finvoice_type', 'PLL_DOMESTIC_INVOICE');
		$this->recordModel->set($this->recordModel->getModule()->getSequenceNumberFieldName(), $this->row['NUMER']);
		$this->loadFromFieldMap();
		$this->loadDeliveryAddress('b');
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

	/**
	 * Convert payment method to system format.
	 *
	 * @param string $value
	 * @param array  $params
	 *
	 * @return string
	 */
	protected function convertPaymentMethods(string $value, array $params): string
	{
		if (isset(self::PAYMENT_METHODS_MAP[$value])) {
			return self::PAYMENT_METHODS_MAP[$value];
		}
		$fieldModel = $this->recordModel->getField($params['fieldName']);
		$key = array_search(mb_strtolower($value), array_map('mb_strtolower', $fieldModel->getPicklistValues()));
		if (empty($key)) {
			$fieldModel->setPicklistValues([$value]);
			$key = $value;
		}
		return $key ?? '';
	}

	/**
	 * Convert date to system format.
	 *
	 * @param string $value
	 * @param array  $params
	 *
	 * @return string
	 */
	protected function convertDate(string $value, array $params): string
	{
		$value = explode(' ', $value);
		return $value[0];
	}

	/**
	 * Load delivery address.
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	protected function loadDeliveryAddress(string $key): void
	{
		$row = (new \App\Db\Query())->select(['dbo.MIEJSCE_DOSTAWY.*'])->from('dbo.DOSTAWA')
			->leftJoin('dbo.MIEJSCE_DOSTAWY', 'dbo.DOSTAWA.ID_MIEJSCA_DOSTAWY = dbo.MIEJSCE_DOSTAWY.ID_MIEJSCA_DOSTAWY')
			->where(['dbo.DOSTAWA.ID_DOKUMENTU_HANDLOWEGO' => $this->row['DOK_KOREKTY'] ? $this->row['ID_DOK_ORYGINALNEGO'] : $this->waproId])
			->one($this->controller->getDb());
		if ($row) {
			$this->recordModel->set('addresslevel1' . $key, $this->convertCountry($row['SYM_KRAJU']));
			$this->recordModel->set('addresslevel5' . $key, $row['MIEJSCOWOSC']);
			$this->recordModel->set('addresslevel7' . $key, $row['KOD_POCZTOWY']);
			$this->recordModel->set('addresslevel8' . $key, $row['ULICA_LOKAL']);
			$this->recordModel->set('company_name_' . $key, $row['FIRMA']);
			if ($row['ODBIORCA']) {
				[$firstName, $lastName] = explode(' ', $row['ODBIORCA'], 2);
				$this->recordModel->set('first_name_' . $key, $firstName);
				$this->recordModel->set('last_name_' . $key, $lastName);
			}
			$params = ['fieldName' => 'phone_' . $key];
			$phone = $this->convertPhone($row['TEL'], $params);
			$this->recordModel->set($params['fieldName'], $phone);
		}
	}

	/**
	 * Load inventory items.
	 *
	 * @return void
	 */
	protected function loadInventory(): void
	{
		$inventory = $this->getInventory();
		if (!$this->recordModel->isNew()) {
			$oldInventory = $this->recordModel->getInventoryData();
			foreach ($oldInventory as $oldSeq => $oldItem) {
				foreach ($inventory as $seq => $item) {
					$same = true;
					foreach ($item as $name => $value) {
						if ($same) {
							$same = isset($oldItem[$name]) && $value == $oldItem[$name];
						}
					}
					if ($same && $oldItem) {
						$inventory[$seq] = $oldItem;
						unset($oldInventory[$oldSeq]);
						continue 2;
					}
				}
			}
		}
		$this->recordModel->initInventoryData($inventory);
		if (isset($inventory[0]['name'])) {
			$this->recordModel->set('subject', \App\Record::getLabel($inventory[0]['name'], true) ?: '-');
		}
	}

	/**
	 * Get inventory items.
	 *
	 * @return array
	 */
	protected function getInventory(): array
	{
		$currencyId = $this->getBaseCurrency()['currencyId'];
		if (!empty($this->row['DOK_WAL'])) {
			$currencyId = $this->convertCurrency($this->row['SYM_WAL'], []);
		}
		$currencyParam = \App\Json::encode($this->getCurrencyParam($currencyId));
		$dataReader = (new \App\Db\Query())->select(['ID_ARTYKULU', 'ILOSC', 'KOD_VAT', 'CENA_NETTO',  'JEDNOSTKA', 'OPIS', 'RABAT', 'RABAT2', 'CENA_NETTO_WAL'])
			->from('dbo.POZYCJA_DOKUMENTU_MAGAZYNOWEGO')
			->where(['ID_DOK_HANDLOWEGO' => $this->waproId])
			->createCommand($this->controller->getDb())->query();
		$inventory = [];
		while ($row = $dataReader->read()) {
			$productId = $this->findRelationship($row['ID_ARTYKULU'], ['tableName' => 'ARTYKUL']);
			if (!$productId) {
				$productId = $this->addProduct($row['ID_ARTYKULU']);
			}
			$inventory[] = [
				'name' => $productId,
				'qty' => $row['ILOSC'],
				'price' => $this->row['DOK_WAL'] ? $row['CENA_NETTO_WAL'] : $row['CENA_NETTO'],
				'comment1' => trim($row['OPIS']),
				'unit' => $this->convertUnitName($row['JEDNOSTKA'], ['fieldName' => 'usageunit', 'moduleName' => 'Products']),
				'discountmode' => 1,
				'discountparam' => \App\Json::encode([
					'aggregationType' => ['individual', 'additional'],
					'individualDiscount' => empty((float) $row['RABAT']) ? 0 : (-$row['RABAT']),
					'individualDiscountType' => 'percentage',
					'additionalDiscount' => empty((float) $row['RABAT2']) ? 0 : (-$row['RABAT2']),
				]),
				'taxmode' => 1,
				'taxparam' => \App\Json::encode(
					$this->getGlobalTax($row['KOD_VAT']) ? [
						'aggregationType' => 'global',
						'globalTax' => (float) $row['KOD_VAT'],
					] : [
						'aggregationType' => 'individual',
						'individualTax' => (float) $row['KOD_VAT'],
					]
				),
				'discount_aggreg' => 2,
				'currency' => $currencyId,
				'currencyparam' => $currencyParam,
			];
		}
		return $inventory;
	}

	/**
	 * Get currency param.
	 *
	 * @param int $currencyId
	 *
	 * @return array
	 */
	protected function getCurrencyParam(int $currencyId): array
	{
		$baseCurrency = $this->getBaseCurrency();
		$defaultCurrencyId = $baseCurrency['default']['id'];
		if (!empty($this->row['DATA_KURS_WAL'])) {
			$date = $this->convertDate($this->row['currencyDate'], []);
		} else {
			$date = \vtlib\Functions::getLastWorkingDay(date('Y-m-d', strtotime('-1 day', strtotime($this->row['saleDate']))));
		}
		$params = [
			$defaultCurrencyId => ['date' => $date, 'value' => 1.0, 'conversion' => 1.0],
		];
		$info = ['date' => $date];
		if ($currencyId != $defaultCurrencyId) {
			if (empty($this->row['PRZELICZNIK_WAL'])) {
				$value = \Settings_CurrencyUpdate_Module_Model::getCleanInstance()->getCRMConversionRate($currencyId, $defaultCurrencyId, $date);
				$info['value'] = empty($value) ? 1.0 : round($value, 5);
				$info['conversion'] = empty($value) ? 1.0 : round(1 / $value, 5);
			} else {
				$info['value'] = round($this->row['PRZELICZNIK_WAL'], 5);
				$info['conversion'] = round(1 / $this->row['PRZELICZNIK_WAL'], 5);
			}
			$params[$currencyId] = $info;
		}
		return $params;
	}

	/**
	 * Add a product when it does not exist in CRM.
	 *
	 * @param int $id
	 *
	 * @return int
	 */
	protected function addProduct(int $id): int
	{
		return $this->controller->getSynchronizer('Products')->importRecordById($id);
	}

	/** {@inheritdoc} */
	public function getCounter(): int
	{
		return (new \App\Db\Query())->from('dbo.DOKUMENT_HANDLOWY')->where(['ID_TYPU' => 1])->count('*', $this->controller->getDb());
	}
}
