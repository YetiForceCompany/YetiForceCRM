<?php

/**
 * Example of special function
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/// Variable calling a function => #special_function#example#end_special_function#
/// function MUST have the same name as FILE
class Pdf_Example extends Vtiger_SpecialFunction_Pdf
{

	public $permittedModules = ['all'];

	public function process($module, $id, Vtiger_PDF_Model $pdf)
	{
		$db = PearDatabase::getInstance();
		/// Sample Query
		$sql = $db->query('select accountname from vtiger_account LIMIT 5');

		//Build data table
		$content = '<br/><table border="1">';
		while ($row = $db->fetch_array($sql)) {
			$content .= '<tr><td align="center"> Account Name </td><td>' . $row['accountname'] . '</td></tr>';
		}
		$content .= '</table><br/>';
		return $content;
	}
}
