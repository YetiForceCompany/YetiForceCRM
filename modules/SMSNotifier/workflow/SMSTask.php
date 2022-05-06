<?php
/**
 * SMS Notifier workflow task file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * SMS Notifier workflow task class.
 */
class VTSMSTask extends VTTask
{
	/** @var bool Execute Immediately */
	public $executeImmediately = true;

	/** @var array Fields */
	public function getFieldNames()
	{
		return ['content', 'sms_recepient'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		if (\App\Integrations\SMSProvider::isActiveProvider()) {
			$moduleName = 'SMSNotifier';
			$recordModelTemp = \Vtiger_Record_Model::getCleanInstance($moduleName)->set('message', $this->content);
			if (\in_array($recordModel->getModuleName(), $recordModelTemp->getField('related_to')->getReferenceList())) {
				$recordModelTemp->set('related_to', $recordModel->getId());
			}

			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			$recepient = $textParser->setContent($this->sms_recepient)->parse()->getContent();
			$recepients = array_unique(explode(',', $recepient));
			foreach ($recepients as $phoneNumber) {
				$phoneNumber = preg_replace_callback('/[^\d\+]/s', function () {
					return '';
				}, strip_tags($phoneNumber));
				if ($phoneNumber) {
					$recordModel = clone $recordModelTemp;
					$recordModel->set('phone', $phoneNumber)->save();
				}
			}
		}
	}
}
