<?php

/**
 * OSSPasswords save action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_Save_Action extends Vtiger_Save_Action
{
	/**
	 * Function to save record.
	 *
	 * @param \App\Request $request - values of the record
	 *
	 * @return Vtiger_Record_Model - record Model of saved record
	 */
	public function saveRecord(App\Request $request)
	{
		$recordModel = $this->getRecordModelFromRequest($request);
		// check if encryption is enabled
		$config = false;
		if (file_exists('modules/OSSPasswords/config.ini.php')) {
			$config = parse_ini_file('modules/OSSPasswords/config.ini.php');
		}

		//check if password was edited with hidden password
		$properPassword = $recordModel->get('password');
		// edit mode
		if (!$recordModel->isNew()) {
			if ('**********' == $properPassword) { // hidden password sent in edit mode, get the correct one
				if ($config) { // when encryption is on
					$properPassword = (new \App\Db\Query())->select(['pass' => new \yii\db\Expression('AES_DECRYPT(`password`, :configKey)', [':configKey' => $config['key']])])->from('vtiger_osspasswords')->where(['osspasswordsid' => $recordModel->getId()])->scalar();
				} else {  // encryption mode is off
					$properPassword = (new \App\Db\Query())->select(['pass' => 'password'])->from('vtiger_osspasswords')->where(['osspasswordsid' => $recordModel->getId()]);
				}
			}
			$recordModel->set('password', $properPassword);
			$recordModel->save();

			// after save we check if encryption is active
			if ($config) {
				\App\Db::getInstance()->createCommand()
					->update('vtiger_osspasswords', [
						'password' => new \yii\db\Expression('AES_ENCRYPT(:properPass,:configKey)', [':properPass' => $properPassword, ':configKey' => $config['key']])
					], ['osspasswordsid' => $recordModel->getId()])
					->execute();
			}
		} else {
			$recordModel->save();
			if ($config) { // when encryption is on
				\App\Db::getInstance()->createCommand()
					->update('vtiger_osspasswords', [
						'password' => new \yii\db\Expression('AES_ENCRYPT(`password`,:configKey)', [':configKey' => $config['key']])
					], ['osspasswordsid' => $recordModel->getId()])
					->execute();
			}
		}
		if ($request->getBoolean('relationOperation')) {
			$relationId = $request->isEmpty('relationId') ? false : $request->getInteger('relationId');
			if ($relationModel = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($request->getByType('sourceModule', 2)), $recordModel->getModule(), $relationId)) {
				$relationModel->addRelation($request->getInteger('sourceRecord'), $recordModel->getId());
			}
		}
		return $recordModel;
	}
}
