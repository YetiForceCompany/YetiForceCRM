<?php
/**
 * Email duplicate checker handler file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Email duplicate checker handler class.
 */
class Contacts_DuplicateEmailChecker_Handler
{
	/** @var bool Allow record to be written */
	const ALLOW_SAVE = false;

	/** @var bool Search archived and deleted (true - Yes, false - No) */
	const TRASH_ARCHIVE = true;

	/** @var array A list of additional information about the record */
	const FIELDS_DETAILS = [
		'Contacts' => ['firstname', 'lastname', 'parent_id', 'assigned_user_id'],
		'Accounts' => ['accountname', 'kod', 'assigned_user_id'],
		'Leads' => ['company', 'kod', 'assigned_user_id'],
	];

	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 * @param array            $handler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler, array $handler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$response = ['result' => true];
		$values = [];
		foreach ($recordModel->getModule()->getFieldsByType('email', true) as $fieldModel) {
			if (($value = $recordModel->get($fieldModel->getName())) && $fieldModel->isViewable()) {
				$values[] = $value;
			}
		}
		if ($values) {
			$modules = explode(',', $handler['include_modules']);
			foreach ($modules as $moduleName) {
				$queryGenerator = new \App\QueryGenerator($moduleName);
				if (self::TRASH_ARCHIVE) {
					$queryGenerator->setStateCondition('All');
				}
				$queryGenerator->setFields(array_merge(['id'], self::FIELDS_DETAILS[$moduleName] ?? []))->permissions = false;
				if ($moduleName === $recordModel->getModuleName() && $recordModel->getId()) {
					$queryGenerator->addCondition('id', $recordModel->getId(), 'n');
				}
				$fields = [];
				foreach ($queryGenerator->getModuleModel()->getFieldsByType('email', true) as $fieldName => $fieldModel) {
					$queryGenerator->addCondition($fieldName, $values, 'e', false);
					$fields[$fieldName] = $fieldModel;
				}
				if ($row = $queryGenerator->createQuery()->one()) {
					$label = '';
					foreach (self::FIELDS_DETAILS[$recordModel->getModuleName()] ?? [] as $fieldName) {
						$fieldModel = $recordModel->getModule()->getFieldByName($fieldName);
						if ('' !== $recordModel->get($fieldName) && $fieldModel->isViewable()) {
							$label .= '<br>' . $fieldModel->getFullLabelTranslation() . ': ' . $recordModel->getDisplayValue($fieldName);
						}
					}
					if (self::ALLOW_SAVE) {
						$response = [
							'result' => false,
							'type' => 'confirm',
							'hash' => hash('sha256', implode('|', $recordModel->getData())),
							'message' => App\Language::translateArgs(
								'LBL_DUPLICATE_EMAIL_ADDRESS',
								$moduleName,
								\App\Language::translate($moduleName, $moduleName)
							) . '<br>' .
							\App\Record::getHtmlLink($row['id'], $moduleName) . $label
						];
					} else {
						$response = [
							'result' => false,
							'message' => App\Language::translateArgs(
								'LBL_DUPLICATE_EMAIL_ADDRESS',
								$moduleName,
								\App\Language::translate($moduleName, $moduleName),
							) . '<br>' .
							\App\Record::getHtmlLink($row['id'], $moduleName) . $label
						];
					}
					break;
				}
			}
		}
		return $response;
	}
}
