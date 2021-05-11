<?php
/**
 * Pdf templates info.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

/**
 * PdfTemplates class.
 */
class PdfTemplates extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public function checkPermission(): void
	{
		parent::checkPermission();
		$moduleName = $this->controller->request->getModule();
		if (!\Api\Portal\Privilege::isPermitted($moduleName, 'ExportPdf')) {
			throw new \Api\Core\Exception("No permissions for action {$moduleName}:ExportPdf", 405);
		}
	}

	/**
	 * Get method.
	 *
	 * @return array
	 */
	public function get()
	{
		$moduleName = $this->controller->request->getModule();
		$recordId = $this->controller->request->getInteger('record');
		$handlerClass = \Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
		$pdfModel = new $handlerClass();
		$templates = $pdfModel->getActiveTemplatesForRecord($recordId, 'Detail', $moduleName);
		$templatesData = [];
		foreach ($templates as $template) {
			$templatesData[$template->getId()] = [
				'id' => $template->getId(),
				'name' => \App\Language::translate($template->getName(), $template->get('module_name')),
				'second_name' => \App\Language::translate($template->get('secondary_name'), $template->get('module_name')),
				'default' => $template->get('default')
			];
		}
		return $templatesData;
	}
}
