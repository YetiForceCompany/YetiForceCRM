<?php
/**
 * Model file responsible for products stocktaking import.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Model class responsible for products stocktaking import.
 */
class Products_Stocktaking_Model
{
	/**
	 * CSV file path.
	 *
	 * @var string
	 */
	private $path;
	/**
	 * Parse CSV instance.
	 *
	 * @var \ParseCsv\Csv
	 */
	private $parseCsv;
	/**
	 * Storage id.
	 *
	 * @var int
	 */
	private $storage;
	/**
	 * EAN/SKU column seq.
	 *
	 * @var int
	 */
	private $eanColumnSeq;
	/**
	 * Qty column seq.
	 *
	 * @var int
	 */
	private $qtyColumnSeq;
	/**
	 * Import temp key.
	 *
	 * @var string
	 */
	private $importKey;

	/**
	 * Get active storages.
	 *
	 * @return string[]
	 */
	public static function getStorage(): array
	{
		return (new \App\Db\Query())->select(['u_#__istorages.istorageid', 'u_#__istorages.subject'])
			->from('u_#__istorages')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = u_#__istorages.istorageid')
			->where(['vtiger_crmentity.deleted' => 0, 'u_#__istorages.storage_status' => 'PLL_ACTIVE'])
			->createCommand()->queryAllByGroup(0);
	}

	/**
	 * Load CSV file by request.
	 *
	 * @param string $filePath
	 *
	 * @return self
	 */
	public static function load(string $filePath): self
	{
		$self = new self();
		$self->path = $filePath;
		$self->parseCsv = new \ParseCsv\Csv();
		$self->parseCsv->use_mb_convert_encoding = true;
		$fileEncoding = \strtoupper(mb_detect_encoding(file_get_contents($filePath), ['UTF-8', 'ISO-8859-1'], true));
		if ($fileEncoding !== \App\Config::main('default_charset', 'UTF-8')) {
			$self->parseCsv->encoding($fileEncoding, \App\Config::main('default_charset', 'UTF-8'));
		}
		return $self;
	}

	/**
	 * Load CSV file by session key.
	 *
	 * @param string $importKey
	 *
	 * @return self
	 */
	public static function loadByKey(string $importKey): self
	{
		$path = \App\Session::get($importKey);
		if (!file_exists($path)) {
			throw new \App\Exceptions\NoPermitted('LBL_RECORD_NOT_FOUND', 406);
		}
		$self = self::load($path);
		$self->importKey = $importKey;
		return $self;
	}

	/**
	 * Get file columns.
	 *
	 * @return array
	 */
	public function analyzeFile(): array
	{
		$this->parseCsv->auto($this->path);
		$randomKey = 'filePath' . App\Encryption::generatePassword(5);
		$target = App\Fields\File::getTmpPath() . $randomKey;
		move_uploaded_file($this->path, $target);
		\App\Session::set($randomKey, $target);
		return [
			'count' => \count($this->parseCsv->data),
			'encoding' => $this->parseCsv->input_encoding,
			'randomKey' => $randomKey,
			'column' => $this->parseCsv->titles,
		];
	}

	/**
	 * Get file columns.
	 *
	 * @param App\Request $request
	 *
	 * @return array
	 */
	public function compare(App\Request $request): array
	{
		$this->parseCsv->heading = false;
		$this->parseCsv->auto($this->path);
		$this->storage = $request->getInteger('storage');
		$this->eanColumnSeq = $request->getInteger('skuColumnSeq');
		$this->qtyColumnSeq = $request->getInteger('qtyColumnSeq');
		$headers = array_shift($this->parseCsv->data);
		$products = $this->getProducts();
		$toUpdate = $notFound = [];
		$update = $same = 0;
		foreach ($this->parseCsv->data as $row) {
			$ean = $row[$this->eanColumnSeq];
			if ($product = ($products[$ean] ?? null)) {
				$crmStock = (float) $product['qtyinstock'];
				$fileStock = (float) $row[$this->qtyColumnSeq];
				if ($crmStock !== $fileStock) {
					++$update;
					$toUpdate[$product['id']] = [
						'ean' => $ean,
						'difference' => $crmStock - $fileStock,
						'fileStock' => $fileStock,
						'crmStock' => $crmStock,
						'data' => array_combine($headers, $row),
					];
				} else {
					++$same;
				}
			} else {
				$notFound[] = $ean;
			}
		}
		if ($toUpdate) {
			\App\Json::save(\App\Fields\File::getTmpPath() . 'json' . $this->importKey, $toUpdate);
		} else {
			unlink($this->path);
		}
		return [
			'notFound' => implode(', ', $notFound),
			'counterNotFound' => \count($notFound),
			'update' => $update,
			'same' => $same,
			'toUpdate' => $toUpdate,
		];
	}

	/**
	 * Get all products stock.
	 *
	 * @return array
	 */
	private function getProducts(): array
	{
		if (0 === $this->storage) {
			$queryGenerator = new \App\QueryGenerator('Products');
			$queryGenerator->setFields(['ean', 'id', 'qtyinstock']);
			$products = $queryGenerator->createQuery()->createCommand()->queryAllByGroup(1);
		} else {
			$referenceInfo = \Vtiger_Relation_Model::getReferenceTableInfo('Products', 'IStorages');
			$products = (new \App\Db\Query())->select([
				'ean' => 'vtiger_products.ean',
				'id' => $referenceInfo['table'] . '.' . $referenceInfo['rel'],
				'qtyinstock' => $referenceInfo['table'] . '.qtyinstock',
			])->from($referenceInfo['table'])
				->innerJoin('vtiger_products', "{$referenceInfo['table']}.{$referenceInfo['rel']} = vtiger_products.productid")
				->innerJoin('vtiger_crmentity', 'vtiger_products.productid = vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0, "{$referenceInfo['table']}.{$referenceInfo['base']}" => $this->storage])
				->createCommand()->queryAllByGroup(1);
		}
		return $products;
	}

	/**
	 * Import of stock adjustments.
	 *
	 * @param App\Request $request
	 *
	 * @return array
	 */
	public function import(App\Request $request): array
	{
		$this->storage = $request->getInteger('storage');
		$this->toUpdate = \App\Json::read(\App\Fields\File::getTmpPath() . 'json' . $this->importKey);
		unlink(\App\Fields\File::getTmpPath() . 'json' . $this->importKey);
		unlink($this->path);
		if (0 === $this->storage) {
			$return = $this->updateStockInProduct();
		} else {
			$return = $this->updateStockInStorage($request->getByType('recordName', \App\Purifier::TEXT));
		}
		return $return;
	}

	/**
	 * Update stock (qtyinstock) in product.
	 *
	 * @return array
	 */
	public function updateStockInProduct(): array
	{
		$i = 0;
		foreach ($this->toUpdate as $crmId => $product) {
			$recordModel = Vtiger_Record_Model::getInstanceById($crmId, 'Products');
			if (!$recordModel->isViewable()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			$recordModel->set('qtyinstock', $product['fileStock']);
			$recordModel->save();
			++$i;
		}
		return ['product' => $i];
	}

	/**
	 * Update stock in storage (IGIN,IIDN).
	 *
	 * @param string $name
	 *
	 * @return int[]
	 */
	public function updateStockInStorage(string $name): array
	{
		$igin = $iidn = [];
		foreach ($this->toUpdate as $crmId => $product) {
			if ($product['difference'] > 0) {
				$igin[$crmId] = $product;
			} else {
				$product['difference'] = -$product['difference'];
				$iidn[$crmId] = $product;
			}
		}
		$return = [];
		if ($igin) {
			$recordModel = Vtiger_Record_Model::getCleanInstance('IGIN');
			$recordModel->set('subject', $name)->set('storageid', $this->storage)->set('igin_status', 'PLL_IN_REALIZATION')->set('acceptance_date', date('Y-m-d'));
			$recordModel->initInventoryData($this->buildInventoryData($igin, 'IGIN'), false);
			$recordModel->save();
			$return['igin'] = $recordModel->getId();
		}
		if ($iidn) {
			$recordModel = Vtiger_Record_Model::getCleanInstance('IIDN');
			$recordModel->set('subject', $name)->set('storageid', $this->storage)->set('iidn_status', 'PLL_IN_REALIZATION')->set('acceptance_date', date('Y-m-d'));
			$recordModel->initInventoryData($this->buildInventoryData($iidn, 'IIDN'), false);
			$recordModel->save();
			$return['iidn'] = $recordModel->getId();
		}
		return $return;
	}

	/**
	 * Build inventory items data.
	 *
	 * @param array  $rows
	 * @param string $moduleName
	 *
	 * @return array
	 */
	private function buildInventoryData(array $rows, string $moduleName): array
	{
		$items = [];
		$itemsSeq = 0;
		foreach ($rows as $crmId => $product) {
			++$itemsSeq;
			$productDetails = Vtiger_Inventory_Action::getRecordDetail($crmId, null, $moduleName, 'name')[$crmId];
			$items[$itemsSeq] =
			array_merge($productDetails['autoFields'], [
				'name' => $crmId,
				'ean' => $product['ean'],
				'qty' => $product['difference'],
				'price' => $productDetails['price'],
				'total' => $productDetails['price'] * $product['difference'],
				'comment1' => $this->buildComment($product)
			]);
		}
		return $items;
	}

	/**
	 * Build comment.
	 *
	 * @param array $row
	 *
	 * @return string
	 */
	private function buildComment(array $row): string
	{
		$comment = "{$row['crmStock']} >> {$row['fileStock']}<br/>\n";
		foreach ($row['data'] as $key => $value) {
			$comment .= "$key: $value<br/>\n";
		}
		return $comment;
	}
}
