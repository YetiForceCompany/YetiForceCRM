<?php
/**
 * Encryption for modules.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Encryptions;

/**
 * Class for encrypting modules data.
 */
class Module extends \App\Encryption
{
	/** {@inheritdoc} */
	public static function getInstance(int $target = \App\Encryption::TARGET_SETTINGS)
	{
		if ($target === static::TARGET_SETTINGS) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||target', 406);
		}
		$row = (new \App\Db\Query())->from(static::TABLE_NAME)->where(['target' => $target])->one(\App\Db::getInstance('admin'));
		$instance = new static();
		if ($row) {
			$instance->set('method', $row['method']);
			$instance->set('vector', $row['pass']);
			$instance->set('target', (int) $row['target']);
		}
		$instance->set('pass', \App\Config::module(\App\Module::getModuleName($target), 'encryptionPass'));
		return $instance;
	}

	/**
	 * Function to change password for encryption.
	 *
	 * @param string $method
	 * @param string $password
	 * @param string $vector
	 * @param int    $target
	 */
	public static function recalculatePasswords(string $method, string $password, string $vector, int $target)
	{
		$dbAdmin = \App\Db::getInstance('admin');
		$decryptInstance = static::getInstance($target);
		if (!$decryptInstance->getTarget() || ($decryptInstance->get('method') === $method && $decryptInstance->get('vector') === $vector && $decryptInstance->get('pass') === $password)) {
			$dbAdmin->createCommand()->update(self::TABLE_NAME, ['status' => self::STATUS_ACTIVE], ['target' => $target])->execute();
			return;
		}
		$encryptInstance = (new static())
			->set('method', $method)
			->set('vector', $vector)
			->set('pass', $password)
			->set('target', $target);

		$db = \App\Db::getInstance();
		$transactionAdmin = $dbAdmin->beginTransaction();
		$transactionBase = $db->beginTransaction();

		try {
			$moduleName = \App\Module::getModuleName($target);
			$pauser = \App\Pauser::getInstance("Encryption-{$moduleName}");
			$lastId = (int) $pauser->getValue();

			$pwdFields = \Vtiger_Module_Model::getInstance($moduleName)->getFieldsByUiType(99);
			$queryGenerator = $decryptInstance->getQueryGenerator($lastId);
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();

			if (!$dataReader->count()) {
				$pauser->destroy();
			}
			while ($row = $dataReader->read()) {
				$recordId = $row['id'];
				foreach ($pwdFields as $fieldModel) {
					$value = $valueRaw = $row[$fieldModel->getName()] ?? '';
					if (!empty($value)) {
						$value = $decryptInstance->decrypt($value);
						if (!$decryptInstance->isEmpty('method') && !$decryptInstance->isActive()) {
							throw new \App\Exceptions\AppException('ERR_IMPOSSIBLE_DECRYPT');
						}
					}
					$value = $encryptInstance->encrypt($value, true);
					if (empty($value) && '' !== $valueRaw && $method) {
						throw new \App\Exceptions\AppException('ERR_IMPOSSIBLE_ENCRYPT');
					}
					$db->createCommand()->update($fieldModel->getTableName(), [$fieldModel->getColumnName() => $value], [$queryGenerator->getColumnName('id') => $recordId])->execute();
				}
				$pauser->setValue((string) $recordId);
			}
			$dataReader->close();

			if ($decryptInstance->getQueryGenerator((int) $pauser->getValue())->createQuery()->exists()) {
				(new \App\BatchMethod(['method' => __CLASS__ . '::recalculatePasswords', 'params' => [$method,  $password,  $vector,  $target, microtime()]]))->save();
			} else {
				(new \App\ConfigFile('module', \App\Module::getModuleName($target)))
					->set('encryptionMethod', $method)
					->set('encryptionPass', $password)
					->create();
				$dbAdmin->createCommand()->update(self::TABLE_NAME, [
					'method' => $method,
					'pass' => $vector,
					'status' => self::STATUS_ACTIVE,
				], ['target' => $target])->execute();
				$pauser->destroy();
			}

			$transactionBase->commit();
			$transactionAdmin->commit();
			\App\Cache::clear();
		} catch (\Throwable $e) {
			$transactionBase->rollBack();
			$transactionAdmin->rollBack();
			throw $e;
		}
	}

	/**
	 * Gets query.
	 *
	 * @param int $lastId
	 *
	 * @return \App\QueryGenerator
	 */
	public function getQueryGenerator(int $lastId = null): \App\QueryGenerator
	{
		$limit = 50000;
		$moduleName = \App\Module::getModuleName($this->getTarget());
		$pwdFields = \Vtiger_Module_Model::getInstance($moduleName)->getFieldsByUiType(99);
		$fields = array_keys($pwdFields);
		$fields[] = 'id';
		$queryGenerator = (new \App\QueryGenerator($moduleName))->setFields($fields);
		$queryGenerator->permissions = false;
		$queryGenerator->setStateCondition('All');
		if (!$pwdFields) {
			$queryGenerator->addNativeCondition((new \yii\db\Expression('0 > 1')));
		}
		$queryGenerator->addNativeCondition(['>', $queryGenerator->getColumnName('id'), $lastId]);
		$queryGenerator->setOrder('id', \App\Db::ASC);
		$queryGenerator->setLimit($limit);
		return $queryGenerator;
	}

	/**
	 * Checks if encrypt or decrypt is possible.
	 *
	 * @param bool $testMode
	 *
	 * @return bool
	 */
	public function isActive(bool $testMode = false)
	{
		$method = \App\Config::module(\App\Module::getModuleName($this->getTarget()), 'encryptionMethod');
		return !(
			!\function_exists('openssl_encrypt')
			|| $this->isEmpty('method')
			|| ($this->get('method') !== $method && !$testMode)
			|| !\in_array($this->get('method'), static::getMethods())
		);
	}
}
