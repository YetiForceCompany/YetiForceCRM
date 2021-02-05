<?php

/**
 * OSSPasswords save action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_Save_Action extends Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function saveRecord(App\Request $request)
	{
		$this->getRecordModelFromRequest($request);
		$eventHandler = $this->record->getEventHandler();
		foreach ($eventHandler->getHandlers(\App\EventHandler::EDIT_VIEW_PRE_SAVE) as $handler) {
			if (!(($response = $eventHandler->triggerHandler($handler))['result'] ?? null)) {
				throw new \App\Exceptions\NoPermittedToRecord($response['message'], 406);
			}
		}
		// check if encryption is enabled
		$config = false;
		if (file_exists('modules/OSSPasswords/config.ini.php')) {
			$config = parse_ini_file('modules/OSSPasswords/config.ini.php');
		}

		//check if password was edited with hidden password
		$properPassword = $this->record->get('password');
		// edit mode
		if (!$this->record->isNew()) {
			if ('**********' == $properPassword) { // hidden password sent in edit mode, get the correct one
				if ($config) { // when encryption is on
					$properPassword = (new \App\Db\Query())->select(['pass' => new \yii\db\Expression('AES_DECRYPT(`password`, :configKey)', [':configKey' => $config['key']])])->from('vtiger_osspasswords')->where(['osspasswordsid' => $this->record->getId()])->scalar();
				} else {  // encryption mode is off
					$properPassword = (new \App\Db\Query())->select(['pass' => 'password'])->from('vtiger_osspasswords')->where(['osspasswordsid' => $this->record->getId()]);
				}
			}
			$this->record->set('password', $properPassword);
			$this->record->save();

			// after save we check if encryption is active
			if ($config) {
				\App\Db::getInstance()->createCommand()
					->update('vtiger_osspasswords', [
						'password' => new \yii\db\Expression('AES_ENCRYPT(:properPass,:configKey)', [':properPass' => $properPassword, ':configKey' => $config['key']])
					], ['osspasswordsid' => $this->record->getId()])
					->execute();
			}
		} else {
			$this->record->save();
			if ($config) { // when encryption is on
				\App\Db::getInstance()->createCommand()
					->update('vtiger_osspasswords', [
						'password' => new \yii\db\Expression('AES_ENCRYPT(`password`,:configKey)', [':configKey' => $config['key']])
					], ['osspasswordsid' => $this->record->getId()])
					->execute();
			}
		}
		if ($request->getBoolean('relationOperation')) {
			$relationId = $request->isEmpty('relationId') ? false : $request->getInteger('relationId');
			if ($relationModel = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($request->getByType('sourceModule', 2)), $this->record->getModule(), $relationId)) {
				$relationModel->addRelation($request->getInteger('sourceRecord'), $this->record->getId());
			}
		}
	}
}
