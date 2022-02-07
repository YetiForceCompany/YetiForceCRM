<?php
/**
 * Tools for Token class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Fields;

/**
 * Token class.
 */
class Token
{
	/**
	 * Sets token data.
	 *
	 * @param string $fieldName
	 * @param string $moduleName
	 */
	public static function setTokens(string $fieldName, string $moduleName)
	{
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$fieldModel = $moduleModel->getFieldByName($fieldName);
		if ($fieldModel && $fieldModel->isActiveField()) {
			$limit = 5000;
			$dataReader = (new \App\QueryGenerator($moduleName))
				->addCondition($fieldModel->getName(), '', 'y')
				->setFields(['id'])
				->createQuery()
				->createCommand()
				->query();
			while ($recordId = $dataReader->readColumn(0)) {
				$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				$token = $fieldModel->getUITypeModel()->generateToken();
				$recordModel->set($fieldModel->getName(), $token)->setDataForSave([$fieldModel->getTableName() => [$fieldModel->getColumnName() => $token]])->save();
				if (!$limit) {
					break;
				}
				--$limit;
			}
			(new \App\BatchMethod(['method' => __METHOD__, 'params' => [$fieldName, $moduleName, time()]]))->save();
		}
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
