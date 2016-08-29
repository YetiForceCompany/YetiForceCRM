<?php
/**
 * Email Template Task Class
 * @package YetiForce.WorkflowTask
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTEmailTemplateTask extends VTTask
{

	// Sending email takes more time, this should be handled via queue all the time.
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return array("template", "attachments", "email", "copy_email");
	}

	public function doTask($entity)
	{
		$util = new VTWorkflowUtils();
		$admin = $util->adminUser();
		$ws_id = $entity->getId();
		$module = $entity->getModuleName();
		$parts = explode('x', $ws_id);
		$entityId = $parts[1];

		if (is_numeric($this->template) && $this->template > 0) {
			if (strpos($this->email, '=') === false) {
				$email = $entity->get($this->email);
			} else {
				$emaildata = explode("=", $this->email);
				$parentRecord = $entity->get($emaildata[0]);
				$parentRecord = explode('x', $parentRecord);
				$parentRecord = $parentRecord[1];
				if (is_numeric($parentRecord) && $parentRecord != '' && $parentRecord != 0) {
					$Record_Model = Vtiger_Record_Model::getInstanceById($parentRecord, $emaildata[1]);
					$email = $Record_Model->get($emaildata[2]);
					$emailMod = $emaildata[1];
					if ($emaildata[1] == 'Contacts') {
						$notifilanguage = $Record_Model->get('notifilanguage');
					}
				}
			}
			if ($email != '') {
				$data = array(
					'id' => $this->template,
					'to_email' => $email,
					'to_email_mod' => $emailMod,
					'notifilanguage' => $notifilanguage,
					'module' => $module,
					'record' => $entityId,
					'cc' => $TASK_OBJECT->copy_email,
				);
				$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
				$recordModel->sendMailFromTemplate($data);
			}
		}
	}
}
