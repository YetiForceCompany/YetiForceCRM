<?php
/**
 * PDF viewer widget file.
 *
 * @package Widget
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * PDF viewer class.
 */
class Vtiger_PDFViewer_Widget extends Vtiger_Basic_Widget
{
	/**
	 * {@inheritdoc}
	 */
	public function isPermitted(): bool
	{
		return parent::isPermitted() && \Vtiger_PDF_Model::getTemplatesByModule($this->Module);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getWidget()
	{
		$this->Config['url'] = 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showPDF';
		$this->Config['tpl'] = 'PDFViewerContainer.tpl';
		return parent::getWidget();
	}

	/**
	 * {@inheritdoc}
	 */
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
		$fields = [];
		if ($this->Record) {
			$handlerClass = \Vtiger_Loader::getComponentClassName('Model', 'PDF', $this->moduleModel->getName());
			$pdfModel = new $handlerClass();
			$params['uitype'] = 16;
			$params['picklistValues'] = [];
			$templates = $pdfModel->getActiveTemplatesForRecord($this->Record, 'Detail', $this->moduleModel->getName());
			foreach ($templates as $key => $pdfTemplate) {
				$params['picklistValues'][$key] = \App\Language::translate($pdfTemplate->get('primary_name'), $this->moduleModel->getName());
			}
			$fields[] = \Vtiger_Field_Model::init($this->moduleModel->getName(), $params, 'template');
		}
		return $fields;
	}
}
