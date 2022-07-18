<?php

/**
 * WAPRO ERP products synchronizer file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Wapro\Synchronizer;

/**
 * WAPRO ERP products synchronizer class.
 */
class Products extends \App\Integrations\Wapro\Synchronizer
{
	/** {@inheritdoc} */
	const NAME = 'LBL_PRODUCTS';

	/** {@inheritdoc} */
	const SEQUENCE = 4;

	/** {@inheritdoc} */
	protected $fieldMap = [
		'NAZWA' => ['fieldName' => 'productname', 'fn' => 'decode'],
		'STAN' => 'qtyinstock',
		'STAN_MINIMALNY' => 'reorderlevel',
		'STAN_MAKSYMALNY' => 'qtyindemand',
		'INDEKS_KATALOGOWY' => ['fieldName' => 'mfr_part_no', 'fn' => 'decode'],
		'INDEKS_HANDLOWY' => ['fieldName' => 'serial_no', 'fn' => 'decode'],
		'INDEKS_PRODUCENTA' => 'vendor_part_no',
		'KOD_KRESKOWY' => 'ean',
		'OPIS' => 'description',
		'WAGA' => 'weight',
		'category' => ['fieldName' => 'pscategory', 'fn' => 'convertCategory'],
		'VAT_SPRZEDAZY' => ['fieldName' => 'taxes', 'fn' => 'convertTaxes'],
		'unitName' => ['fieldName' => 'usageunit', 'fn' => 'convertUnitName', 'moduleName' => 'Products'],
		'CENA_ZAKUPU_BRUTTO' => ['fieldName' => 'purchase', 'fn' => 'convertPrice'],
		'total' => ['fieldName' => 'unit_price', 'fn' => 'convertPrice'],
	];

	/** @var array Cache form products */
	protected $cache = [];

	/** {@inheritdoc} */
	public function process(): int
	{
		$query = (new \App\Db\Query())->select([
			'dbo.ARTYKUL.*',
			'category' => 'dbo.KATEGORIA_ARTYKULU_TREE.NAZWA',
			'unitName' => 'dbo.JEDNOSTKA.SKROT',
			'total' => 'dbo.CENA_ARTYKULU.CENA_NETTO',
			'gross' => 'dbo.CENA_ARTYKULU.CENA_BRUTTO',
		])->from('dbo.ARTYKUL')
			->leftJoin('dbo.KATEGORIA_ARTYKULU_TREE', 'dbo.ARTYKUL.ID_KATEGORII_TREE = dbo.KATEGORIA_ARTYKULU_TREE.ID_KATEGORII_TREE')
			->leftJoin('dbo.JEDNOSTKA', 'dbo.ARTYKUL.ID_JEDNOSTKI = dbo.JEDNOSTKA.ID_JEDNOSTKI')
			->leftJoin('dbo.CENA_ARTYKULU', 'dbo.ARTYKUL.ID_ARTYKULU = dbo.CENA_ARTYKULU.ID_ARTYKULU AND dbo.ARTYKUL.ID_CENY_DOM = dbo.CENA_ARTYKULU.ID_CENY');
		$pauser = \App\Pauser::getInstance('WaproProductsLastId');
		if ($val = $pauser->getValue()) {
			$query->where(['>', 'dbo.ARTYKUL.ID_ARTYKULU', $val]);
		}
		$lastId = $s = $e = $i = $u = 0;
		foreach ($query->batch(100, $this->controller->getDb()) as $rows) {
			$lastId = 0;
			foreach ($rows as $row) {
				$this->waproId = $row['ID_ARTYKULU'];
				$this->cache[$this->waproId] = $row;
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
		if ($id = $this->findInMapTable($this->waproId, 'ARTYKUL')) {
			$this->recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Products');
		} else {
			$this->recordModel = \Vtiger_Record_Model::getCleanInstance('Products');
			$this->recordModel->setDataForSave([\App\Integrations\Wapro::RECORDS_MAP_TABLE_NAME => [
				'wtable' => 'ARTYKUL',
			]]);
		}
		$this->recordModel->set('wapro_id', $this->waproId);
		$this->recordModel->set('discontinued', 1);
		$this->loadFromFieldMap();
		if ($this->skip) {
			return 0;
		}
		$this->recordModel->save();
		\App\Cache::save('WaproMapTable', "{$this->waproId}|ARTYKUL", $this->recordModel->getId());
		if ($id) {
			return $this->recordModel->getPreviousValue() ? 1 : 3;
		}
		return 2;
	}

	/**
	 * Convert price to system format.
	 *
	 * @param string $value
	 * @param array  $params
	 *
	 * @return string
	 */
	protected function convertPrice(string $value, array $params): string
	{
		$currency = $this->getBaseCurrency();
		return \App\Json::encode([
			'currencies' => [
				$currency['currencyId'] => ['price' => $value]
			],
			'currencyId' => $currency['currencyId']
		]);
	}

	/**
	 * Convert category to system format.
	 *
	 * @param string $value
	 * @param array  $params
	 *
	 * @return string
	 */
	protected function convertCategory(string $value, array $params): string
	{
		$fieldModel = $this->recordModel->getField($params['fieldName']);
		$list = \App\Fields\Tree::getPicklistValue($fieldModel->getFieldParams(), $fieldModel->getModuleName());
		$key = array_search($value, $list);
		return $key ?? '';
	}

	/**
	 * Convert taxes to system format.
	 *
	 * @param string $value
	 * @param array  $params
	 *
	 * @return string
	 */
	protected function convertTaxes(string $value, array $params): string
	{
		return $this->getGlobalTax($value, true);
	}

	/**
	 * Import record by id.
	 *
	 * @param int $id
	 *
	 * @return int
	 */
	public function importRecordById(int $id): int
	{
		$this->row = $this->getRecordById($id);
		$this->waproId = $this->row['ID_ARTYKULU'];
		try {
			$this->importRecord();
		} catch (\Throwable $th) {
			$this->logError($th);
			throw $th;
		}
		return $this->recordModel->getId();
	}

	/**
	 * Get product record by record id.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function getRecordById(int $id): array
	{
		if (isset($this->cache[$id])) {
			return $this->cache[$id];
		}
		return $this->cache[$id] = (new \App\Db\Query())->select([
			'dbo.ARTYKUL.*',
			'category' => 'dbo.KATEGORIA_ARTYKULU_TREE.NAZWA',
			'unitName' => 'dbo.JEDNOSTKA.SKROT',
			'total' => 'dbo.CENA_ARTYKULU.CENA_NETTO',
			'gross' => 'dbo.CENA_ARTYKULU.CENA_BRUTTO',
		])->from('dbo.ARTYKUL')
			->leftJoin('dbo.KATEGORIA_ARTYKULU_TREE', 'dbo.ARTYKUL.ID_KATEGORII_TREE = dbo.KATEGORIA_ARTYKULU_TREE.ID_KATEGORII_TREE')
			->leftJoin('dbo.JEDNOSTKA', 'dbo.ARTYKUL.ID_JEDNOSTKI = dbo.JEDNOSTKA.ID_JEDNOSTKI')
			->leftJoin('dbo.CENA_ARTYKULU', 'dbo.ARTYKUL.ID_CENY_DOM = dbo.CENA_ARTYKULU.ID_CENY')
			->where(['dbo.ARTYKUL.ID_ARTYKULU' => $id])
			->one($this->controller->getDb()) ?? [];
	}

	/** {@inheritdoc} */
	public function getCounter(): int
	{
		return (new \App\Db\Query())->from('dbo.ARTYKUL')->count('*', $this->controller->getDb());
	}
}
