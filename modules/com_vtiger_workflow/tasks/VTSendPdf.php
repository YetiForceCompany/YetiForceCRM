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
		$recordId = $recordModel->getId();
		$module = $recordModel->getModuleName();
		if ((is_numeric($this->pdf_tpl) && $this->pdf_tpl != 0) && (is_numeric($this->email_tpl) && $this->email_tpl != 0)) {
			if (false === strpos($this->email_fld, '=')) {
				$email = $recordModel->get($this->email_fld);
			} else {
				list($parentIdFieldName, $relModuleName, $relModuleField) = explode('=', $this->email_fld);
				$relRecord = $recordModel->get($parentIdFieldName);
				if ($module === $relModuleName) {
					$relRecord = $recordId;
				}
				if (is_numeric($relRecord) && intval($relRecord) > 0) {
					$recordModel = Vtiger_Record_Model::getInstanceById($relRecord, $relModuleName);
					$email = $recordModel->get($relModuleField);
				}
			}
		}
		if (!empty($email)) {
			$templateRecord = Vtiger_PDF_Model::getInstanceById($this->pdf_tpl);
			$fileName = vtlib\Functions::slug($templateRecord->getName()) . '_' . time() . '.pdf';
			$pdfFile = 'cache' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $fileName;
			Vtiger_PDF_Model::exportToPdf($recordId, $module, $this->pdf_tpl, $pdfFile, 'F');
			\App\Mailer::sendFromTemplate([
				'template' => $this->email_tpl,
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
