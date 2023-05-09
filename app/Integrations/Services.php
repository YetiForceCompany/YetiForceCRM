<?php
/**
 * Services main integration file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Integrations;

/**
 * Services main integration class.
 */
class Services
{
	/** @var string Basic table name */
	public const TABLE_NAME = 'w_#__servers';
	/** @var string Services type */
	public const OAUTH = 'OAuth';

	/**
	 * Get services by type.
	 *
	 * @param string $name
	 * @param bool   $onlyActive
	 *
	 * @return array
	 */
	public static function getByType(string $name, bool $onlyActive = true): array
	{
		$services = [];
		$query = (new \App\Db\Query())->from(self::TABLE_NAME)->where(['type' => $name]);
		if ($onlyActive) {
			$query->andWhere(['status' => 1]);
		}
		$dataReader = $query->createCommand(\App\Db::getInstance('webservice'))->query();
		while ($row = $dataReader->read()) {
			$services[$row['id']] = $row;
		}
		return $services;
	}

	public static function getById(int $serviceId): array
	{
		$service = (new \App\Db\Query())
			->from(self::TABLE_NAME)->where(['id' => $serviceId])
			->one(\App\Db::getInstance('webservice'));
		return $service ?: [];
	}
}
