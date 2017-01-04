<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
require_once('modules/com_vtiger_workflow/VTEntityCache.php');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTSendPdf extends VTTask
{

	public $executeImmediately = true;

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$templateId = $this->pdf_tpl;
		$emailTemplateId = $this->email_tpl;
		$emailField = $this->email_fld;
		$recordId = $recordModel->getId();
		$module = $recordModel->getModuleName();

		if ((is_numeric($templateId) && $templateId != 0) && (is_numeric($emailTemplateId) && $emailTemplateId != 0)) {
			if (false === strpos($emailField, '=')) {
				$email = $recordModel->get($emailField);
			} else {
				list($parentIdFieldName, $relModuleName, $relModuleField) = explode('=', $emailField);
				$relRecord = $recordModel->get($parentIdFieldName);
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
			\App\Mailer::sendFromTemplate([
				'template' => $emailTemplateId,
				'moduleName' => $module,
				'recordId' => $recordId,
				'to' => $email,
				'attachments' => [$pdfFile => $fileName],
			]);
		}
	}

	public function getFieldNames()
	{
		return ['pdf_tpl', 'email_tpl', 'email_fld'];
	}
}
