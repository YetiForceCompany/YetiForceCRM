<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTSendPdf extends VTTask
{

	public $executeImmediately = true;

	public function doTask($entity)
	{
		$templateId = $this->pdf_tpl;
		$emailTemplateId = $this->email_tpl;
		$emailField = $this->email_fld;
		$recordId = explode('x', $entity->getId())[1];
		$module = $entity->getModuleName();

		if ((is_numeric($templateId) && $templateId != 0) && (is_numeric($emailTemplateId) && $emailTemplateId != 0)) {
			if (false === strpos($emailField, '=')) {
				$email = $entity->get($emailField);
			} else {
				list($parentIdFieldName, $relModuleName, $relModuleField) = explode('=', $emailField);
				$relRecord = explode('x', $entity->get($parentIdFieldName))[1];
				if ($module == $relModuleName) {
					$relRecord = $recordId;
				}
				if (is_numeric($relRecord) && intval($relRecord) > 0) {
					$recordModel = Vtiger_Record_Model::getInstanceById($relRecord, $relModuleName);
					$email = $recordModel->get($relModuleField);
				}
			}
		}

		if (!empty($email)) {
			$templateRecord = Vtiger_PDF_Model::getInstanceById($templateId);
			$fileName = vtlib\Functions::slug($templateRecord->getName()) . '_' . time() . '.pdf';
			$pdfFile = 'cache' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $fileName;
			Vtiger_PDF_Model::exportToPdf($recordId, $module, $templateId, $pdfFile, 'F');
			$data = [
				'id' => $emailTemplateId,
				'to_email' => $email,
				'module' => $module,
				'record' => $recordId,
				'attachment_src' => [$fileName => $pdfFile],
			];
			$emailTemplateRecord = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
			$emailTemplateRecord->sendMailFromTemplate($data);
		}
	}

	public function getFieldNames()
	{
		return ['pdf_tpl', 'email_tpl', 'email_fld'];
	}
}
