<?php
/**
 * File to read and save configuration for integration with Comarch.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Comarch;

use App\Db\Query;
use App\Integrations\Comarch;

/**
 * Class to read and save configuration for integration with Comarch.
 */
class Config extends \App\Base
{
	/**
	 * Get all servers.
	 *
	 * @return array
	 */
	public static function getAllServers(): array
	{
		if (\App\Cache::has('Comarch|getAllServers', '')) {
			return \App\Cache::get('Comarch|getAllServers', '');
		}
		$servers = [];
		$dataReader = (new Query())->from(Comarch::TABLE_NAME)->where(['status' => 1])
			->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$row['password'] = \App\Encryption::getInstance()->decrypt($row['password']);
			$servers[$row['id']] = $row;
		}
		$dataReader->close();
		\App\Cache::save('Comarch|getAllServers', '', $servers);
		return $servers;
	}

	/**
	 * Get server configuration by id.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getServer(int $id): array
	{
		$servers = self::getAllServers();
		if (empty($servers[$id])) {
			throw new \App\Exceptions\AppException('Comarch Server not found: ' . $id);
		}
		$server = $servers[$id];
		if (!empty($server['attributes'])) {
			$server['attributes'] = \App\Json::decode($server['attributes']);
		}
		return $server;
	}

	/**
	 * Function to get object to read configuration.
	 *
	 * @param int $serverId
	 *
	 * @return self
	 */
	public static function getInstance(int $serverId): self
	{
		$db = \App\Db::getInstance('admin');
		$instance = new self();
		$instance->setData(
			array_merge(
				self::getServer($serverId),
				\App\Config::component('IntegrationComarch', null, []),
				(new Query())->select(['name', 'value'])->from(Comarch::CONFIG_TABLE_NAME)
					->where(['server_id' => $serverId])
					->createCommand($db)->queryAllByGroup(),
				[
					'maps' => (new Query())->select(['map', 'class'])->from(Comarch::MAP_TABLE_NAME)
						->where(['server_id' => $serverId])
						->createCommand($db)->queryAllByGroup()
				]
			),
		);
		return $instance;
	}

	/**
	 * Save in db last scanned id.
	 *
	 * @param string      $type
	 * @param string|null $name
	 * @param int|null    $id
	 *
	 * @throws \yii\db\Exception
	 */
	public function setScan(string $type, ?string $name = null, ?int $id = null): void
	{
		$dbCommand = \App\Db::getInstance('admin')->createCommand();
		if (null !== $name) {
			$data = ['name' => "{$type}_last_{$name}",	'value' => $id];
		} else {
			$data = ['name' => $type . '_start_date', 'value' => date('Y-m-d H:i:s')];
		}
		if (!(new Query())->from(Comarch::CONFIG_TABLE_NAME)
			->where(['server_id' => $this->get('id'), 'name' => $data['name']])->exists()) {
			$data['server_id'] = $this->get('id');

			$dbCommand->insert(Comarch::CONFIG_TABLE_NAME, $data)->execute();
		}
		$dbCommand->update(
			Comarch::CONFIG_TABLE_NAME,
			$data,
			['server_id' => $this->get('id'), 'name' => $data['name']]
		)->execute();
		$this->set($data['name'], $data['value']);
	}

	/**
	 * Set end scan.
	 *
	 * @param string $type
	 * @param string $date
	 *
	 * @throws \yii\db\Exception
	 */
	public function setEndScan(string $type, string $date): void
	{
		$dbCommand = \App\Db::getInstance('admin')->createCommand();
		if (!$date) {
			$date = date('Y-m-d H:i:s');
		}
		$saveData = [
			[
				'name' => $type . '_end_date',
				'value' => $date,
			], [
				'name' => $type . '_last_id',
				'value' => 0,
			], [
				'name' => $type . '_last_page',
				'value' => null,
			],
		];
		foreach ($saveData as $data) {
			if (!(new Query())->from(Comarch::CONFIG_TABLE_NAME)
				->where(['server_id' => $this->get('id'), 'name' => $data['name']])->exists()) {
				$data['server_id'] = $this->get('id');
				$dbCommand->insert(Comarch::CONFIG_TABLE_NAME, $data)->execute();
			} else {
				$dbCommand->update(
					Comarch::CONFIG_TABLE_NAME,
					$data,
					['server_id' => $this->get('id'), 'name' => $data['name']]
				)->execute();
			}
			$this->set($data['name'], $data['value']);
		}
	}

	/**
	 * Get last scan information.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function getLastScan(string $type): array
	{
		return [
			'id' => (int) $this->get($type . '_last_id') ?? 0,
			'page' => $this->get($type . '_last_page') ?: null,
			'start_date' => $this->get($type . '_start_date') ?? false,
			'end_date' => $this->get($type . '_end_date') ?? false,
		];
	}

	/**
	 * Reload integration with Comarch.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public static function reload(int $id): void
	{
		\App\Db::getInstance('admin')->createCommand()
			->delete(Comarch::CONFIG_TABLE_NAME, ['server_id' => $id])
			->execute();
	}

	/**
	 * Get available connectors.
	 *
	 * @return string[]
	 */
	public static function getConnectors(): array
	{
		$connectors = [];
		$iterator = new \DirectoryIterator(__DIR__ . '/Connector');
		foreach ($iterator as $item) {
			if ($item->isFile() && 'Base.php' !== $item->getFilename() && 'php' === $item->getExtension()
			) {
				$name = $item->getBasename('.php');
				$className = '\\App\\Integrations\\Comarch\\Connector\\' . $name;
				$connectors[$name] = $className::NAME;
			}
		}
		return $connectors;
	}
}
