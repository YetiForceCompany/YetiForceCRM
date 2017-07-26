<?php

/**
 * PaymentsIn PaymentsImport view  class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class PaymentsIn_PaymentsImport_View extends Vtiger_Index_View
{

	public function process(\App\Request $request)
	{

		$type = [];
		$bank = [];
		foreach (new DirectoryIterator('modules/PaymentsIn/helpers') as $file) {
			if (!$file->isDot()) {
				if (strpos($file->getFilename(), '.php') !== false) {
					$type[] = str_replace(".php", "", $file->getFilename());
				}
			}
		}
		$bank[] = 'Default';
		foreach (new DirectoryIterator('modules/PaymentsIn/helpers/subclass') as $file) {
			if (!$file->isDot()) {
				if (strpos($file->getFilename(), '.php') !== false) {
					$banks = explode('_', str_replace(".php", "", $file->getFilename()));
					$status = false;
					$countBank = count($bank);
					for ($i = 0; $i < $countBank; $i++) {
						if ($bank[$i] == $banks[1])
							$status = true;
					}
					if ($status !== true)
						$bank[] = $banks[1];
				}
			}
		}

		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('TYP', $type);
		$viewer->assign('BANK', $bank);
		echo $viewer->view('Import.tpl', $moduleName, true);
	}
}
