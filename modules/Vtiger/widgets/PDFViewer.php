<?php
/**
 * PDF viewer widget file.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * PDF viewer class.
 */
class Vtiger_PDFViewer_Widget extends Vtiger_Basic_Widget
{
	/** {@inheritdoc} */
	public function isPermitted(): bool
	{
		return parent::isPermitted() && Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModuleActionPermission($this->Module, 'ExportPdf') && \Vtiger_PDF_Model::getTemplatesByModule($this->Module);
	}

	/** {@inheritdoc} */
	public function getWidget()
	{
		$this->Config['url'] = 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showPDF';
		$this->Config['tpl'] = 'PDFViewerContainer.tpl';
		if (!empty($this->Data['action']) && $this->Record && ($fields = $this->getCustomFields())) {
			$this->Config['url'] .= '&template=' . $fields['template']->get('fieldvalue');
		}
		return parent::getWidget();
	}

	/** {@inheritdoc} */
	public function getConfigTplName()
	{
		return 'PDFViewerConfig';
	}

	/**
	 * Gets custom fields.
	 *
	 * @return array
	 */
	public function getCustomFields(): array
	{
		if ($this->Record && !isset($this->fields)) {
			$this->fields = [];
			$handlerClass = \Vtiger_Loader::getComponentClassName('Model', 'PDF', $this->moduleModel->getName());
			$pdfModel = new $handlerClass();
			$params['uitype'] = 16;
			$params['picklistValues'] = [];
			$templates = $pdfModel->getActiveTemplatesForRecord($this->Record, 'Detail', $this->moduleModel->getName());
			foreach ($templates as $key => $pdfTemplate) {
				$params['picklistValues'][$key] = \App\Language::translate($pdfTemplate->get('primary_name'), $this->moduleModel->getName());
			}
			if (!empty($this->Data['action'])) {
				$params['fieldvalue'] = array_key_first($templates);
			}
			$this->fields['template'] = \Vtiger_Field_Model::init($this->moduleModel->getName(), $params, 'template');
		}

		return $this->fields ?? [];
	}
}
