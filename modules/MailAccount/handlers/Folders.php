<?php
/**
 * MailAccount handler.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * MailAccount folders class.
 */
class MailAccount_Folders_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$fieldModel = $recordModel->getField('folders');
		if ($recordModel->isNew() || false !== $recordModel->getPreviousValue($fieldModel->getName())) {
			$dbCommand = \App\Db::getInstance()->createCommand();
			$previous = (new \App\Db\Query())->select(['name'])->from(\App\Mail\Scanner::FOLDER_TABLE)
				->where(['user_id' => $recordModel->getId()])->column();
			$current = \App\Json::decode($recordModel->get($fieldModel->getName()) ?: '[]');
			if ($remove = array_diff($previous, $current)) {
				$dbCommand->delete(\App\Mail\Scanner::FOLDER_TABLE, ['user_id' => $recordModel->getId(), 'name' => $remove])->execute();
			}
			if ($add = array_diff($current, $previous)) {
				$insert = [];
				foreach ($add as $folderName) {
					$insert[] = [$recordModel->getId(), $folderName];
				}
				$dbCommand->batchInsert(\App\Mail\Scanner::FOLDER_TABLE, ['user_id', 'name'], $insert)->execute();
			}
		}
	}
}
