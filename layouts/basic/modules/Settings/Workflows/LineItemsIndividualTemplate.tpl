{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}

{strip}
	<div class="template-contents">
		<table border=1 style="font-size:11px; width:100%; table-layout: fixed; border-collapse: collapse;">
			<thead>
                <tr bgcolor=#c0c0c0>
                    <td style="text-align: center">
                        <strong>Item Code</strong>
                    </td>
                    <td style="text-align: center">
                        <strong>Item Name</strong>
                    </td>
                    <td style="text-align: center">
                        <strong>Quantity</strong>
                    </td>
                    <td style="text-align: center">
                        <strong>List Price</strong>
                    </td>
                    <td style="text-align: center">
                        <strong>Item Total</strong>
                    </td>
                    <td style="text-align: center">
                        <strong>Discount</strong>
                    </td>
                    <td style="text-align: center">
                        <strong>Total After Discount</strong>
                    </td>
                    <td style="text-align: center">
                        <strong>Tax</strong>
                    </td>
                    <td style="text-align: center">
                        <strong>Total</strong>
                    </td>
                </tr>
			</thead>
			<tbody>
                <!-- foreach item=LINEITEM from=$RECORD.LINEITEMS -->
                <tr>
                    <td align=right style="text-align: center; vertical-align : top; word-wrap: break-word;">
                        {literal} {$LINEITEM.hdnProductcode} {/literal}   
                    </td>
                    <td align=right style="text-align: center; vertical-align : top; word-wrap: break-word;">
                        {literal} {$LINEITEM.productName} {/literal}   
                    </td>
                    <td align=right style="text-align: right; vertical-align : top; word-wrap: break-word;">
                        {literal} {$LINEITEM.quantity} {/literal}
                    </td>
                    <td align=right style="text-align: right; vertical-align : top; word-wrap: break-word;">
                        {literal} {$LINEITEM.listprice} {/literal}
                    </td>
                    <td align=right style="text-align: right; vertical-align : top; word-wrap: break-word;">
                        {literal} {$LINEITEM.productTotal} {/literal}
                    </td>
                    <td align=right style="text-align: right; vertical-align : top; word-wrap: break-word;">
                        {literal}{$LINEITEM.discount_amount}{/literal}&nbsp;&nbsp;
                        ({literal}{$LINEITEM.discount_percent}{/literal} %)
                    </td>
					<td align=left style="text-align: right; vertical-align : top; word-wrap: break-word;">
                        {literal} {$LINEITEM.totalAfterDiscount} {/literal}
                    </td>
                    <td align=right style="text-align: right; vertical-align : top; word-wrap: break-word;">
                        {literal} {$LINEITEM.taxTotal} {/literal}&nbsp; &nbsp;
                        ({literal}{$LINEITEM.item_tax_totalpercent}{/literal} %)
                    </td>
                    <td align=right style="text-align: right; vertical-align : top; word-wrap: break-word;">
                        {literal} {$LINEITEM.netPrice} {/literal}
                    </td>
				</tr>
				<!-- /foreach -->
                <tr>
                    <td colspan=8 style="word-wrap: break-word; text-align: right;">Items Total</td>
                    <td style="text-align: right; word-wrap: break-word;">
                        {literal} {$RECORD.hdnSubTotal} {/literal}
                    </td>
                </tr>
                <tr>
                    <td colspan=8 style="word-wrap: break-word; text-align: right;">Discount({literal}{$RECORD.discount_percentage_final}{/literal}%)</td>
                    <td style="text-align: right; word-wrap: break-word;">
                        {literal}{$RECORD.discountTotal_final}{/literal}
                    </td>
                </tr>
                <tr>
                    <td colspan=8 style="word-wrap: break-word; text-align: right;">Pre Tax Total</td>
                    <td style="text-align: right; word-wrap:  break-word;">
						{literal}{$RECORD.pre_tax_total}{/literal}
                    </td>
                </tr>
                <tr>
                    <td colspan=8 style="word-wrap: break-word; text-align: right;">
                        <span style="font-weight: bold">GRAND TOTAL</span>
                        <strong style=" word-wrap: break-word;">
                            ({literal}{$RECORD.currency_symbol}{/literal})
                        </strong>
                    </td>
                    <td style="text-align: right; word-wrap: break-word;">
                        <strong style=" word-wrap: break-word;">{literal}{$RECORD.hdnGrandTotal}{/literal}</strong>
                    </td>
				</tr>
			</tbody>
		</table>
	</div>
{/strip}               
