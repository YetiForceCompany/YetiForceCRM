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

	/** @var \App\Integrations\Wapro Controller instance. */
	protected $controller;

	/**
	 * Synchronizer constructor.
	 *
	 * @param \App\Integrations\Wapro $controller
	 */
	public function __construct(\App\Integrations\Wapro $controller)
	{
		$this->controller = $controller;
	}

	/**
	 * Main function to execute synchronizer.
	 *
	 * @return void
	 */
	abstract public function process(): void;

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
		$crmId = (new \App\Db\Query())->from(\App\Integrations\Wapro::RECORDS_MAP_TABLE_NAME)->select(['crmid'])->where(['wtable' => $table, 'wid' => $id])->scalar();
		\App\Cache::save('WaproMapTable', $cacheKey, $crmId);
		return $crmId ?: null;
	}

	/**
	 * Add log to db.
	 *
	 * @param string      $category
	 * @param ?\Throwable $ex
	 *
	 * @return void
	 */
	public function log(string $category, ?\Throwable $ex = null): void
	{
		\App\DB::getInstance('log')->createCommand()
			->insert(\App\Integrations\Wapro::LOG_TABLE_NAME, [
				'time' => date('Y-m-d H:i:s'),
				'category' => $ex ? $category : 'info',
				'message' => $ex ? $ex->getMessage() : $category,
				'code' => $ex ? $ex->getCode() : 200,
				'trace' => $ex ? $ex->__toString() : null,
			])->execute();
	}
}
