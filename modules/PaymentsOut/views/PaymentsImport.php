<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com.
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class PaymentsOut_PaymentsImport_View extends Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{

		$type = array();
		$bank = array();
		foreach (new DirectoryIterator('modules/PaymentsOut/helpers') as $file) {
			if (!$file->isDot()) {
				if (strpos($file->getFilename(), '.php') !== false) {
					$type[] = str_replace(".php", "", $file->getFilename());
				}
			}
		}
		$bank[] = 'Default';
		foreach (new DirectoryIterator('modules/PaymentsOut/helpers/subclass') as $file) {
			if (!$file->isDot()) {
				if (strpos($file->getFilename(), '.php') !== false) {
					$banks = explode('_', str_replace(".php", "", $file->getFilename()));
					$status = false;
					$countBank = count($bank);
					for ($i = 0; $i < $countBank; $i++) {
						if ($bank[$i] == $banks[1])
							$status = true;
					}
					if ($status != true)
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
