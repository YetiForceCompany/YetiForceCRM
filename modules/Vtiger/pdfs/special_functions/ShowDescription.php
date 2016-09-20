<?php

/**
 * Special function displaying record description and attentions
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class Pdf_ShowDescription extends Vtiger_SpecialFunction_Pdf
{

	public $permittedModules = ['IIDN', 'IGRN', 'IGDN', 'IGIN', 'ISTDN', 'ISTRN', 'IPreOrder'];

	public function process($module, $id, Vtiger_PDF_Model $pdf)
	{
		$html = '';
		$recordId = $id;
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$fields['description'] = $recordModel->get('description');
		$fields['attention'] = $recordModel->get('attention');
		foreach ($fields as $key => $field) {
			if (empty($field) === false) {
				$html.= '<table style="width:100%; font-size:10px"><tbody><tr><td style="width:10%">';
				$html.= '<strong>' . vtranslate(ucfirst($key)) . '</strong></td>';
				$html.= '<td style="width:90%">' . $field . '</td>';
				$html.= '</tr></tbody></table>';
			}
		}
		if (empty($html) === false) {
			$html.= '<hr/>';
		}
		return $html;
	}
}
