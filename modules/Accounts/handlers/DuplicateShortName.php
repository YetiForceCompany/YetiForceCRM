<?php
/**
 * Short name duplicate checker handler field.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Short name duplicate checker handler class.
 */
class Accounts_DuplicateShortName_Handler
{
	/** @var array List of fields for verification */
	const FIELDS = [
		'Accounts' => ['account_short_name'],
	];

	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$response = ['result' => true];
		$values = [];
		foreach (self::FIELDS[$recordModel->getModuleName()] as $fieldName) {
			$fieldModel = $recordModel->getModule()->getFieldByName($fieldName);
			if ($fieldModel->isViewable() && ($value = $recordModel->get($fieldName))) {
				$values[] = $value;
			}
		}
		foreach (self::FIELDS as $moduleName => $fields) {
			$queryGenerator = new \App\QueryGenerator($moduleName);
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id'])->permissions = false;
			if ($moduleName === $recordModel->getModuleName() && $recordModel->getId()) {
				$queryGenerator->addCondition('id', $recordModel->getId(), 'n');
			}
			foreach ($fields as $fieldName) {
				$queryGenerator->addCondition($fieldName, $values, 'e', false);
			}
			if ($queryGenerator->createQuery()->exists()) {
				$response = [
					'result' => false,
					'hoverField' => reset($fields),
					'message' => App\Language::translateArgs(
						'LBL_DUPLICATE_FIELD_VALUE',
						$recordModel->getModuleName(),
						\App\Language::translate('FL_ACCOUNT_SHORT_NAME', $moduleName) . ': ' . implode(',', $values)
					),
				];
				break;
			}
		}
		return $response;
	}
}
