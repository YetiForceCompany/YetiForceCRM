<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
$permitted_modules = array(  'OSSInvoiceCost','OSSCorrectedInvoice','Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder' );
/// ZNACZNIK WYWOLUJACY TE FUNKCJE => #special_function#example#end_special_function#
/// funkcja MUSI miec taką samą nazwe jak PLIK
function get_terms( $module, $id, $templateid ,$content, $tcpdf) {
	$db = PearDatabase::getInstance();
	/// przykladowe zapytanie
	if($module = 'Invoice'){
		$idname = 'invoiceid';
	}elseif($module = 'Quotes'){
		$idname = 'quoteid';
	}elseif($module = 'SalesOrder'){
		$idname = 'salesorderid';
	}elseif($module = 'PurchaseOrder'){
		$idname = 'purchaseorderid';
	}elseif($module = 'OSSInvoiceCost'){
		$idname = 'ossinvoicecostid';
	}elseif($module = 'OSSCorrectedInvoice'){
		$idname = 'osscorrectedinvoiceid';
	}
	$zapytanie = "SELECT terms_conditions FROM vtiger_" . strtolower($module) . " WHERE $idname = $id";
        $result = $db->query($zapytanie, true);
        $warunki = $db->query_result($result, 0, 'terms_conditions');
        
        if ($warunki == '') {
            return '';
        }
	
	$tresc = '';
	$tresc .= '<br/><table border="1" width="535px">
	<tbody>
		<tr>
			<td><p><font size="-1">';
        $tresc .= $warunki;

	$tresc .= '</font></p></td>
		</tr>
	</tbody>
</table>';
	//zwracamy wynik
	return $tresc;
}
?>