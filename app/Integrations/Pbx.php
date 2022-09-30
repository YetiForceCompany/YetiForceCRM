<?php
/**
 * PBX main integration file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations;

/**
 * PBX main integration class.
 */
class Pbx extends \App\Base
{
	/** @var \App\Integrations\Pbx\Base[] Connector Instances. */
	private static $connectors = [];

	/** @var array Cache for default PBX. */
	private static $defaultCache;

	/**
	 * Get pbx connectors.
	 *
	 * @return \App\Integrations\Pbx\Base
	 */
	public static function getConnectors()
	{
		$connectors = [];
		foreach ((new \DirectoryIterator(__DIR__ . \DIRECTORY_SEPARATOR . 'Pbx')) as $fileInfo) {
			$fileName = $fileInfo->getBasename('.php');
			if ('dir' !== $fileInfo->getType() && 'Base' !== $fileName && 'php' === $fileInfo->getExtension()) {
				$className = '\App\Integrations\Pbx\\' . $fileName;
				if (!class_exists($className)) {
					\App\Log::warning('Not found Pbx class');
					continue;
				}
				$instance = new $className(new self());
				$connectors[$fileName] = $instance;
			}
		}
		return $connectors;
	}

	/**
	 * Undocumented function.
	 *
	 * @return array
	 */
	public static function getDefault(): array
	{
		if (isset(self::$defaultCache)) {
			return self::$defaultCache;
		}
		return self::$defaultCache = (new \App\Db\Query())->from('s_#__pbx')->where(['default' => 1])->one() ?: [];
	}

	/**
	 * Whether a call is active with the PBX integration.
	 *
	 * @return bool
	 */
	public static function isActive()
	{
		$phone = \App\User::getCurrentUserModel()->getDetail('phone_crm_extension_extra');
		if (empty($phone)) {
			return false;
		}
		return !empty(self::getDefault());
	}

	/**
	 * Get default pbx instance.
	 *
	 * @return self
	 */
	public static function getDefaultInstance(): self
	{
		$instance = new self();
		if ($data = self::getDefault()) {
			$instance->setData($data);
		}
		return $instance;
	}

	/**
	 * Load user phone.
	 *
	 * @return void
	 */
	public function loadUserPhone(): void
	{
		$this->set('sourcePhone', \App\User::getCurrentUserModel()->getDetail('phone_crm_extension_extra'));
	}

	/**
	 * Perform phone call.
	 *
	 * @param string $targetPhone
	 * @param int    $record
	 *
	 * @throws \Exception
	 *
	 * @return array
	 */
	public function performCall(string $targetPhone, int $record): array
	{
		if ($this->isEmpty('sourcePhone')) {
			throw new \App\Exceptions\AppException('No user phone number');
		}
		if (empty($targetPhone)) {
			throw new \App\Exceptions\AppException('No target phone number');
		}
		$this->set('targetPhone', $targetPhone);
		$this->set('record', $record);
		$connector = $this->getConnector();
		if (empty($connector)) {
			throw new \App\Exceptions\AppException('No PBX connector found');
		}
		return $connector->performCall();
	}

	/**
	 * Get connector instance.
	 *
	 * @return \App\Integrations\Pbx\Base|null
	 */
	public function getConnector(): ?Pbx\Base
	{
		$className = '\App\Integrations\Pbx\\' . $this->get('type');
		if (isset(static::$connectors[$className])) {
			return static::$connectors[$className];
		}
		if (class_exists($className)) {
			return static::$connectors[$className] = new $className($this);
		}
		\App\Log::warning('Not found Pbx class');
		return null;
	}

	/**
	 * Get connector instance.
	 *
	 * @param string $name
	 *
	 * @return \App\Integrations\Pbx\Base|null
	 */
	public static function getConnectorByName(string $name): ?Pbx\Base
	{
		$className = '\App\Integrations\Pbx\\' . $name;
		if (isset(static::$connectors['static|' . $className])) {
			return static::$connectors['static|' . $className];
		}
		if (class_exists($className)) {
			return static::$connectors['static|' . $className] = new $className(new self());
		}
		\App\Log::warning('Not found Pbx class');
		return null;
	}

	/**
	 * Function to get the config param for a given key.
	 *
	 * @param string $key
	 *
	 * @return mixed Value for the given key
	 */
	public function getConfig($key)
	{
		if ($this->isEmpty('paramArray')) {
			$this->set('paramArray', \App\Json::decode($this->get('param')));
		}
		return $this->get('paramArray')[$key] ?? null;
	}

	/**
	 * Searching for a relationship by phone number.
	 *
	 * @param string $phoneNumber
	 *
	 * @return int
	 */
	public function findNumber(string $phoneNumber): int
	{
		$id = 0;
		$phoneNumber = preg_replace('/(?<!^)\+|[^\d+]+/', '', $phoneNumber);
		foreach (\App\Config::component('Pbx', 'phoneSearchField', []) as $moduleName => $fields) {
			if (\App\Module::isModuleActive($moduleName)) {
				$queryGenerator = new \App\QueryGenerator($moduleName);
				$queryGenerator->permissions = false;
				$queryGenerator->setFields(['id']);
				foreach ($fields as $fieldName) {
					$queryGenerator->addCondition($fieldName, $phoneNumber, 'e', false);
				}
				if ($scalar = $queryGenerator->createQuery()->scalar()) {
					$id = $scalar;
					break;
				}
			}
		}
		return $id;
	}
}
