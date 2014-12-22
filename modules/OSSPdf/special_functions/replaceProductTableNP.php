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
$permitted_modules = array('Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder', 'OSSWarehouseReleases', 'OSSWarehouseAdoptions', 'OSSWarehouseReservations');

/// ZNACZNIK WYWOLUJACY TE FUNKCJE => #special_function#example#end_special_function#
/// funkcja MUSI miec taką samą nazwe jak PLIK
function replaceProductTableNP($pdftype, $id, $templateid,$content, $tcpdf) {

    $db = PearDatabase::getInstance();
    require_once( 'include/utils/utils.php' );
    $current_language = Users_Record_Model::getCurrentUserModel()->get('language');
    include("languages/" . $current_language . "/OSSPdf.php");
    require_once( 'include/utils/CommonUtils.php' );
    require_once( 'include/fields/CurrencyField.php' );

    require_once( 'modules/' . $pdftype . '/' . $pdftype . '.php' );

    $focus = new $pdftype();
    $focus->retrieve_entity_info($id, $pdftype);
    $focus->id = $focus->column_fields["record_id"];
    $associated_products = getAssociatedProducts($pdftype, $focus);

    $num_products = count($associated_products);

    $currency_id = $focus->column_fields['currency_id'];
    $pobierz = $db->query("select currency_symbol from vtiger_currency_info where id = '$currency_id'", true);
    $symbol_waluty = $db->query_result($pobierz, 0, "currency_symbol");

    //This $final_details array will contain the final total, discount, Group Tax, S&H charge, S&H taxes
    $final_details = $associated_products[1]['final_details'];

    //getting the Net Total
    $price_subtotal = number_format($final_details["hdnSubTotal"], 2, '.', ',');

    //Final discount amount/percentage
    $discount_amount = $final_details["discount_amount_final"];
    $discount_percent = $final_details["discount_percentage_final"];

    if ($discount_amount != "")
        $price_discount = number_format($discount_amount, 2, '.', ',');
    else if ($discount_percent != "") {
        //This will be displayed near Discount label - used in include/fpdf/templates/body.php
        $final_price_discount_percent = "(" . number_format($discount_percent, 2, '.', ',') . " %)";
        $price_discount = number_format((($discount_percent * $final_details["hdnSubTotal"]) / 100), 2, '.', ',');
    }
    else
        $price_discount = "0.00";

    //Grand Total
    $price_total = number_format($final_details["grandTotal"], 2, '.', ',');


    //To calculate the group tax amount
    if ($final_details['taxtype'] == 'group') {
        $group_tax_total = $final_details['tax_totalamount'];
        $price_salestax = number_format($group_tax_total, 2, '.', ',');

        $group_total_tax_percent = '0.00';
        $group_tax_details = $final_details['taxes'];
        for ($i = 0; $i < count($group_tax_details); $i++) {
            $group_total_tax_percent = $group_total_tax_percent + $group_tax_details[$i]['percentage'];
        }
    }

    $prod_line = array();
    $lines = 0;

    //This is to get all prodcut details as row basis
    for ($i = 1, $j = $i - 1; $i <= $num_products; $i++, $j++) {
        $product_name[$i] = $associated_products[$i]['productName' . $i];
        $subproduct_name[$i] = split("<br>", $associated_products[$i]['subprod_names' . $i]);
        $comment[$i] = $associated_products[$i]['comment' . $i];
        $product_id[$i] = $associated_products[$i]['hdnProductId' . $i];
        $qty[$i] = $associated_products[$i]['qty' . $i];
        $unit_price[$i] = number_format($associated_products[$i]['unitPrice' . $i], 2, '.', ',');
        $list_price[$i] = $associated_products[$i]['listPrice' . $i]; // number_format($associated_products[$i]['listPrice'.$i],2,'.',',');
        $list_pricet[$i] = $associated_products[$i]['listPrice' . $i];
        $discount_total[$i] = $associated_products[$i]['discountTotal' . $i];

        //aded for 5.0.3 pdf changes
        $product_code[$i] = $associated_products[$i]['hdnProductcode' . $i];

        $taxable_total = $qty[$i] * $list_pricet[$i] - $discount_total[$i];
        $producttotal = $taxable_total;
        $total_taxes = '0.00';
        if ($focus->column_fields["hdnTaxType"] == "individual") {
            $total_tax_percent = '0.00';
            //This loop is to get all tax percentage and then calculate the total of all taxes
            for ($tax_count = 0; $tax_count < count($associated_products[$i]['taxes']); $tax_count++) {
                $tax_percent = $associated_products[$i]['taxes'][$tax_count]['percentage'];
                $total_tax_percent = $total_tax_percent + $tax_percent;
                $tax_amount = (($taxable_total * $tax_percent) / 100);
                $total_taxes = $total_taxes + $tax_amount;
            }
            $producttotal = $taxable_total + $total_taxes;
            $product_line[$j]["tax_percentage"] = $total_tax_percent;
            $product_line[$j]["Tax"] = $total_taxes;
            $price_salestax += $total_taxes;
        }
        $prod_total[$i] = $producttotal; // number_format($producttotal,2,'.',',');
        $product_line[$j]["Product Code"] = $product_code[$i];
        $product_line[$j]["Qty"] = $qty[$i];
        $product_line[$j]["Price"] = $list_price[$i];
        $product_line[$j]["Discount"] = $discount_total[$i];
        $product_line[$j]["Total"] = $prod_total[$i];
        $lines++;
        $product_line[$j]["Product Name"] = decode_html($product_name[$i]);

        $prod_line[$j] = 1;
        for ($count = 0; $count < count($subproduct_name[$i]); $count++) {
            if ($lines % 12 != 0) {
                $product_line[$j]["Product Name"] .= "\n" . decode_html($subproduct_name[$i][$count]);
                $prod_line[$j]++;
            } else {
                $j++;
                $product_line[$j]["Product Name"] = decode_html($product_name[$i]);
                $product_line[$j]["Product Name"] .= "\n" . decode_html($subproduct_name[$i][$count]);
                $prod_line[$j] = 2;
                $lines++;
            }
            $lines++;
        }
        if ($comment[$i] != '') {
            $product_line[$j]["Product Name"] .= "\n" . decode_html($comment[$i]);
            $prod_line[$j]++;
            $lines++;
        }
    }
    $price_salestax = number_format($price_salestax, 2, '.', ',');
    $header = Array(vtranslate('LBL_nr', 'OSSPdf'), vtranslate('LBL_productname', 'OSSPdf'), vtranslate('LBL_Quantity', 'OSSPdf'), vtranslate('LBL_price', 'OSSPdf'), vtranslate('LBL_netto', 'OSSPdf'), vtranslate('LBL_rabat', 'OSSPdf'), vtranslate('LBL_vat', 'OSSPdf'), vtranslate('LBL_brutto', 'OSSPdf'));

    $data = Array();
    $i = 0;
    foreach ($product_line as $item) {
        $data[$i++] = Array($i, $item['Product Name'], $item['Qty'], $item['Price'], $item['Price'] * $item['Qty'], $item['Discount'], 'NP', $item['Total']);
    }


    $width = array(30, 245, 35, 45, 45, 45, 45, 45);
    $align = array("center", "center", "center", "center", "center", "center", "center", "center");
    $format = array(0, "s", 0, 2, 2, 2, "np", 2);

    $product_table = '<table border="1" cellpadding="2">';
    $product_table .= '<tr valign="middle">';
    for ($i = 0; $i < count($header); $i++) {
        $product_table .= '<td width="' . $width[$i] . '" height="20" align="' . $align[$i] . '"><b><small>' . $header[$i] . '</small></b></td>';
    }
    $product_table .= '</tr>';

    $align = array("center", "left", "center", "center", "center", "center", "center", "center", "center");

    //Data
    foreach ($data as $row) {
        $product_table .= '<tr>';
        $i = 0;
        foreach ($row as $item) {
            $sum[$i] += (float) $item;
            if ($format[$i] == 's') {
                $itarr = explode("\n\n", $item);
                $item = '<b>' . $itarr[0] . '</b><br/><small>' . $itarr[1] . '</small>';
            } elseif ($format[$i] == 'np') {
                
            } else {
                $currfield = new CurrencyField($item);
                $item = $currfield->getDisplayValue();
            }
            $product_table .= '<td width="' . $width[$i] . '" align="' . $align[$i++] . '"><small>' . $item . '</small></td>';
        }
        $product_table .= '</tr>';
    }
    $product_table .= "</table>";
    $i = 0;


    $mod = strtolower($pdftype);
    if ($mod == 'quotes') {
        $idcol = "quoteid";
    } else {
        $idcol = $mod . "id";
    }

    $sql = "SELECT discount_percent, discount_amount, subtotal, total FROM vtiger_$mod WHERE $idcol = " . $id;
    $result = $db->query($sql, true);
    $grand_total = $db->query_result($result, 0, 'total');
    $subtotal = $db->query_result($result, 0, "subtotal");
    $discount_percent = $db->query_result($result, 0, 'discount_percent');
    $discount_amount = $db->query_result($result, 0, 'discount_amount');

    if ($discount_percent != 0) {
        $discount = $subtotal * ( $discount_percent / 100.0 );
    } else {
        $discount = $discount_amount;
    }
    $currfield = new CurrencyField($grand_total);
    $grand_total = $currfield->getDisplayValue();
    $currfield = new CurrencyField($subtotal);
    $subtotal = $currfield->getDisplayValue();
    $currfield = new CurrencyField($discount);
    $discount = $currfield->getDisplayValue();

    $product_table .= '<table width="535px" border="1" cellpadding="2">
			<tr height="10"><td align="right" valign="middle"><small><b>' . getTranslatedString('Net Total', "OSSPdf") . '</b> : ' . $subtotal . '</small></td></tr>
			<tr valign="middle"><td align="right"><small><b>' . getTranslatedString("Discount Amount", "OSSPdf") . '</b> : ' . $discount . '</small></td></tr>
			<tr valign="middle"><td align="right"><small><b>' . getTranslatedString("Grand Total", "OSSPdf") . '</b> : ' . $grand_total . ' (' . $symbol_waluty . ')</small></td></tr>
		</table><br/>';
//				$currfield = new CurrencyField( $grand_total );
//		$grand_total = $currfield->getDBInsertedValue( $grand_total );
    //	$kwota = $this->slownie( $grand_total );
    //$content = str_replace( "#amount_words#",  $kwota , $content );
    return $product_table;
}

?>