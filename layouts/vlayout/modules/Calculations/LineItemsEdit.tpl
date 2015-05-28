{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}
	<input type="hidden" class="numberOfCurrencyDecimal" value="{$USER_MODEL->get('no_of_currency_decimals')}" />
    <table class="table table-bordered blockContainer lineItemTable" id="lineItemTab">
        <tr>
            <th colspan="8"><span>{vtranslate('LBL_ITEM_DETAILS', $MODULE)}</span></th>
        </tr>
        <tr>
            <td><strong>{vtranslate('LBL_TOOLS',$MODULE)}</strong></td>
            <td><strong>{vtranslate('LBL_ITEM_NAME',$MODULE)}</strong></td>
            <td><strong>{vtranslate('LBL_QTY',$MODULE)}</strong></td>
            <td><strong>{vtranslate('LBL_UNIT',$MODULE)}</strong></td>
            <td><strong>{vtranslate('LBL_LIST_PRICE',$MODULE)}</strong></td>
			<td><strong>{vtranslate('LBL_RBH',$MODULE)}</strong></td>
            <td><strong class="pull-right">{vtranslate('LBL_TOTAL',$MODULE)}</strong></td>
			<td></td>
        </tr>
        <tr id="row0" class="hide lineItemCloneCopy">
            {include file="LineItemsContent.tpl"|@vtemplate_path:'Calculations' row_no=0 data=[]}
        </tr>
        {foreach key=row_no item=data from=$RELATED_PRODUCTS}
            <tr id="row{$row_no}" class="lineItemRow" {if $data["entityType$row_no"] eq 'Products'}data-quantity-in-stock={$data["qtyInStock$row_no"]}{/if}>
                {include file="LineItemsContent.tpl"|@vtemplate_path:'Calculations' row_no=$row_no data=$data}
            </tr>
        {/foreach}
        {if count($RELATED_PRODUCTS) eq 0}
            <tr id="row1" class="lineItemRow">
                {include file="LineItemsContent.tpl"|@vtemplate_path:'Calculations' row_no=1 data=[]}
            </tr>
        {/if}
    </table>
    <div class="row-fluid verticalBottomSpacing">
        <div>
            {if $PRODUCT_ACTIVE eq 'true' && $SERVICE_ACTIVE eq 'true'}
                <div class="btn-toolbar">
                    <span class="btn-group">
                        <button type="button" class="btn addButton" id="addProduct">
                            <span class="icon-plus icon-white"></span><strong>{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
                        </button>
                    </span>
                    <span class="btn-group">
                        <button type="button" class="btn addButton" id="addService">
                            <span class="icon-plus icon-white"></span><strong>{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
                        </button>
                    </span>
                </div>
            {elseif $PRODUCT_ACTIVE eq 'true'}
                <div class="btn-group">
                    <button type="button" class="btn addButton" id="addProduct">
                        <span class="icon-plus icon-white"></span><strong> {vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
                    </button>
                </div>
            {elseif $SERVICE_ACTIVE eq 'true'}
                <div class="btn-group">
                    <button type="button" class="btn addButton" id="addService">
                        <span class="icon-plus icon-white"></span><strong> {vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
                    </button>
                </div>
            {/if}
        </div>
    </div>
    <table class="table table-bordered blockContainer lineItemTable" id="lineItemResult">
        <tr valign="top">
            <td  width="83%">
                <span class="pull-right"><strong>{vtranslate('LBL_GRAND_TOTAL',$MODULE)}</strong></span>
            </td>
            <td>
                <span id="grandTotal" name="grandTotal" class="pull-right grandTotal">{$FINAL.grandTotal}</span>
            </td>
        </tr>
            <tr valign="top">
                <td width="83%" >
                    <div class="pull-right">
                        <strong>{vtranslate('Total Purchase',$MODULE)}</strong>
                    </div>
                </td>
                <td>
                    <span id="total_purchase" name="total_purchase" class="pull-right total_purchase">{$FINAL.total_purchase}</span>
                </td>
            </tr>
            <tr valign="top">
                <td width="83%" >
                    <div class="pull-right">
                        <strong>{vtranslate('Total margin',$MODULE)}</strong>
                    </div>
                </td>
                <td>
                    <span id="total_margin" name="total_margin" class="pull-right total_margin">{$FINAL.total_margin}</span>
                </td>
            </tr>
            <tr valign="top">
                <td width="83%" >
                    <div class="pull-right">
                        <strong>{vtranslate('Total margin Percentage',$MODULE)}</strong>
                    </div>
                </td>
                <td>
                    <span id="total_marginp" name="total_marginp" class="pull-right total_marginp">{$FINAL.total_marginp}</span>
                </td>
            </tr>
    </table>
    <input type="hidden" name="totalProductCount" id="totalProductCount" value="{$row_no}" />
    <input type="hidden" name="total" id="total" value="{$FINAL.grandTotal}" />
{/strip}