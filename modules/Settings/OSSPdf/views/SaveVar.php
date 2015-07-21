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
require_once( 'include/utils/UserInfoUtil.php' );

class Settings_OSSPdf_SaveVar_View extends Settings_Vtiger_Index_View
{

	public function preProcess(Vtiger_Request $request)
	{
		parent::preProcess($request);
	}

	public function process(Vtiger_Request $request)
	{

		include( "modules/OSSPdf/special_functions/" . $request->get('fname') );
		$variableRegex = '/^[ \t]*\\$([^=]+)=([^;]+)/';
		$lines = file("modules/OSSPdf/special_functions/" . $request->get('fname'));

		foreach ($lines as $line_num => $line) {
			if (preg_match($variableRegex, $line, $m)) {
				$nazwa = trim($m[1]);
				$wartosc = trim($m[2]);
				if (isset($variables_list[$nazwa])) {
					$newvalue = '$' . $nazwa . ' = ' . $request->get($nazwa) . ';' . "\n";
					$lines[$line_num] = htmlspecialchars($newvalue);
				}
			}
		}


		file_put_contents("modules/OSSPdf/special_functions/" . $request->get('fname'), $lines);

		header("Location: index.php?module=OSSPdf&view=Index&parent=Settings&block=" . $request->get('block') . "&fieldid=" . $request->get('fieldid'));
	}
}

?>
