<?php
/**
 * Tools for Token class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Fields;

/**
 * Token class.
 */
class Token
{
	/**
	 * Gets token by record ID.
	 *
	 * @param int         $recordId
	 * @param string|null $moduleName
	 *
	 * @return string|null
	 */
	public static function getToken(int $recordId, ?string $moduleName = null): ?string
	{
		$token = '';
		if (\App\Record::isExists($recordId, $moduleName)) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$fieldToken = current($recordModel->getModule()->getFieldsByType('token', true));
			if ($fieldToken && !($token = $recordModel->get($fieldToken->getName()))) {
				$recordModel->set($fieldToken->getName(), $fieldToken->getUITypeModel()->generateToken())->save();
				$token = $recordModel->get($fieldToken->getName());
			}
		}
		return $token;
	}

	/**
	 * Generate token.
	 *
	 * @return string
	 */
	public static function generateToken(): string
	{
		return hash('sha256', microtime(true) . \App\Encryption::generatePassword(20));
	}
}
