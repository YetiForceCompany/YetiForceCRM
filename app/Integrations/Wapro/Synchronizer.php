<?php
/**
 * WAPRO ERP base synchronizer file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Wapro;

/**
 * WAPRO ERP base synchronizer class.
 */
abstract class Synchronizer
{
	/** @var string Provider name | File name. */
	const NAME = null;

	/** @var string[] Map of fields integrating with WAPRO ERP */
	protected $fieldMap = [];

	/** @var \App\Integrations\Wapro Controller instance. */
	protected $controller;

	/** @var string Controller instance. */
	protected $className;

	/** @var \Vtiger_Record_Model Record model instance. */
	protected $recordModel;

	/** @var array Record row. */
	protected $row;

	/** @var bool The flag to skip record creation. */
	protected $skip;

	/** @var bool Information on currency configuration. */
	protected static $currency;

	/**
	 * Synchronizer constructor.
	 *
	 * @param \App\Integrations\Wapro $controller
	 */
	public function __construct(\App\Integrations\Wapro $controller)
	{
		$this->controller = $controller;
		$this->className = substr(static::class, strrpos(static::class, '\\') + 1);
		if (isset($controller->customConfig[$this->className])) {
			$this->fieldMap = $controller->customConfig[$this->className];
		}
	}

	/**
	 * Main function to execute synchronizer.
	 *
	 * @return void
	 */
	abstract public function process(): void;

	/**
	 * Import record.
	 *
	 * @return int
	 */
	abstract public function importRecord(): int;

	/**
	 * Function to get provider name.
	 *
	 * @return string provider name
	 */
	public function getName(): string
	{
		return $this::NAME;
	}

	/**
	 * Find the crm ID for the integration record.
	 *
	 * @param int    $id
	 * @param string $table
	 *
	 * @return int|null
	 */
	public function findInMapTable(int $id, string $table): ?int
	{
		$cacheKey = "$id|$table";
		if (\App\Cache::has('WaproMapTable', $cacheKey)) {
			return \App\Cache::get('WaproMapTable', $cacheKey);
		}
		$crmId = (new \App\Db\Query())->select(['crmid'])->from(\App\Integrations\Wapro::RECORDS_MAP_TABLE_NAME)->where(['wtable' => $table, 'wid' => $id])->scalar();
		\App\Cache::save('WaproMapTable', $cacheKey, $crmId);
		return $crmId ?: null;
	}

	/**
	 * Add error log to db.
	 *
	 * @param \Throwable $ex
	 *
	 * @return void
	 */
	public function logError(\Throwable $ex): void
	{
		\App\Log::error("Error during import record in {$this->className}:\n{$ex->__toString()}", 'Integrations/Wapro');
		\App\DB::getInstance('log')->createCommand()
			->insert(\App\Integrations\Wapro::LOG_TABLE_NAME, [
				'time' => date('Y-m-d H:i:s'),
				'category' => $this->className,
				'message' => \App\TextUtils::textTruncate($ex->getMessage(), 255),
				'error' => true,
				'trace' => \App\TextUtils::textTruncate($ex->__toString(), 65535)
			])->execute();
	}

	/**
	 * Add log to db.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function log(string $message): void
	{
		\App\DB::getInstance('log')->createCommand()
			->insert(\App\Integrations\Wapro::LOG_TABLE_NAME, [
				'time' => date('Y-m-d H:i:s'),
				'category' => $this->className,
				'message' => \App\TextUtils::textTruncate($message, 255),
				'error' => false,
			])->execute();
	}

	/**
	 * Load data from DB based on field map.
	 *
	 * @return void
	 */
	protected function loadFromFieldMap(): void
	{
		foreach ($this->fieldMap as $wapro => $crm) {
			if (\array_key_exists($wapro, $this->row)) {
				if (null === $this->row[$wapro]) {
					continue;
				}
				if (\is_array($crm)) {
					$value = $this->{$crm['fn']}($this->row[$wapro], $crm); // it cannot be on the lower line because it is a reference
					if ($this->skip) {
						break;
					}
					$this->recordModel->set($crm['fieldName'], $value);
				} else {
					$this->recordModel->set($crm, $this->row[$wapro]);
				}
			} else {
				\App\Log::error("No column {$wapro} in the {$this->className}", 'Integrations/Wapro');
			}
		}
	}

	/**
	 * Convert phone to system format.
	 *
	 * @param string $value
	 * @param array  $params
	 *
	 * @return string
	 */
	protected function convertPhone(string $value, array &$params): string
	{
		$fieldModel = $this->recordModel->getField($params['fieldName']);
		$details = $fieldModel->getUITypeModel()->getPhoneDetails($value, 'PL');
		$value = $details['number'];
		if ($params['fieldName'] !== $details['fieldName']) {
			$params['fieldName'] = $details['fieldName'];
		}
		return $value;
	}

	/**
	 * Convert country to system format.
	 *
	 * @param string $value
	 * @param array  $params
	 *
	 * @return string
	 */
	protected function convertCountry(string $value, array $params): string
	{
		return $value ? \App\Fields\Country::getCountryName($value) : '';
	}

	/**
	 * Convert currency to system format.
	 *
	 * @param string $value
	 * @param array  $params
	 *
	 * @return int
	 */
	protected function convertCurrency(string $value, array $params): int
	{
		$currencyId = \App\Fields\Currency::getIdByCode($value);
		if (empty($currencyId)) {
			$currencyId = \App\Fields\Currency::addCurrency($value);
		}
		return $currencyId ?? 0;
	}

	/**
	 * Convert currency to system format.
	 *
	 * @param string $value
	 * @param array  $params
	 *
	 * @return int
	 */
	protected function findRelationship(string $value, array $params): int
	{
		if ($id = $this->findInMapTable($value, $params['tableName'])) {
			return $id;
		}
		if (!empty($params['skipMode'])) {
			$this->skip = true;
		}
		return 0;
	}

	/**
	 * Get information about base currency.
	 *
	 * @return array
	 */
	protected function getBaseCurrency(): array
	{
		if (isset(self::$currency)) {
			return self::$currency;
		}
		$wapro = (new \App\Db\Query())->from('dbo.WALUTA_BAZOWA_V')->one($this->controller->getDb());
		return self::$currency = [
			'wapro' => $wapro,
			'currencyId' => $this->convertCurrency($wapro['SYM_WALUTY'], []),
			'default' => \App\Fields\Currency::getDefault(),
		];
	}
}
