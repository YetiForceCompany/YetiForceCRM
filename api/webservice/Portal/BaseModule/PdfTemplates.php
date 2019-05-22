<?php
/**
 * Pdf templates info.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

/**
 * PdfTemplates class.
 */
class PdfTemplates extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Check permission to method.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	public function checkPermission()
	{
		$result = parent::checkPermission();
		$moduleName = $this->controller->request->getModule('module');
		if (!\Api\Portal\Privilege::isPermitted($moduleName, 'ExportPdf')) {
			throw new \Api\Core\Exception("No permissions for action {$moduleName}:ExportPdf", 405);
		}
		return $result;
	}

	/**
	 * Get method.
	 *
	 * @return array
	 */
	public function get()
	{
		$moduleName = $this->controller->request->getModule('module');
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
