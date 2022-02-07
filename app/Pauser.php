<?php

/**
 * Temporary data handling functionality.
 * It's used in time consuming processes that deal with a large number of entries.
 * It stores data about the last executed element of a given process in order to resume it at a later time while maintaining continuity.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Pauser - temporary data handler class.
 */
class Pauser
{
	/** Table name. */
	private const TABLE_NAME = 's_#__pauser';

	/** @var string Entry name */
	private $key;

	/** @var string Value */
	private $value;

	/**
	 * Return object instance.
	 *
	 * @param string $key
	 *
	 * @return self
	 */
	public static function getInstance(string $key): self
	{
		$instance = new self();
		$instance->setKey($key)->load();
		return $instance;
	}

	/**
	 * Set key name.
	 *
	 * @param string $key
	 *
	 * @return self
	 */
	private function setKey(string $key): self
	{
		$this->key = $key;
		return $this;
	}

	/**
	 * Load data from database.
	 *
	 * @return self
	 */
	private function load(): self
	{
		$val = (new \App\Db\Query())->select(['value'])->from(self::TABLE_NAME)->where(['key' => $this->key])->scalar();
		$this->value = false === $val ? null : $val;
		return $this;
	}

	/**
	 * Set value.
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	public function setValue(string $value): bool
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (null === $this->value) {
			$result = $dbCommand->insert(self::TABLE_NAME, ['value' => $value, 'key' => $this->key])->execute();
		} else {
			$result = $dbCommand->update(self::TABLE_NAME, ['value' => $value], ['key' => $this->key])->execute();
		}
		if ($result) {
			$this->value = $value;
		}
		return (bool) $result;
	}

	/**
	 * Get value.
	 *
	 * @return string|null
	 */
	public function getValue(): ?string
	{
		return $this->value;
	}

	/**
	 * Remove key from database.
	 *
	 * @return bool
	 */
	public function destroy(): bool
	{
		$this->value = null;
		return (bool) \App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, ['key' => $this->key])->execute();
	}
}
