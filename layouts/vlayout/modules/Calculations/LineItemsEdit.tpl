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
            <th colspan="5"><span>{vtranslate('LBL_ITEM_DETAILS', $MODULE)}</span></th>
            <th colspan="3" class="chznDropDown">
                <div class="">
                    <span class="inventoryLineItemHeader">{vtranslate('LBL_CURRENCY', $MODULE)}</span>&nbsp;&nbsp;
                    {assign var=SELECTED_CURRENCY value=$CURRENCINFO}
                    {* Lookup the currency information if not yet set - create mode *}
                    {if $SELECTED_CURRENCY eq ''}
                        {assign var=USER_CURRENCY_ID value=$USER_MODEL->get('currency_id')}
                        {foreach item=currency_details from=$CURRENCIES}
                            {if $currency_details.curid eq $USER_CURRENCY_ID}
                                {assign var=SELECTED_CURRENCY value=$currency_details}
                            {/if}
                        {/foreach}
                    {/if}

                    <select class="chzn-select" id="currency_id" name="currency_id" title="{vtranslate('LBL_CURRENCY', $MODULE)}" style="width: 164px;">
                        {foreach item=currency_details key=count from=$CURRENCIES}
                            <option value="{$currency_details.curid}" class="textShadowNone" data-conversion-rate="{$currency_details.conversionrate}" {if $SELECTED_CURRENCY.currency_id eq $currency_details.curid} selected {/if}>
                                {$currency_details.currencylabel|@getTranslatedCurrencyString} ({$currency_details.currencysymbol})
                            </option>
                        {/foreach}
                    </select>

                    {assign var="RECORD_CURRENCY_RATE" value=$RECORD_STRUCTURE_MODEL->getRecord()->get('conversion_rate')}
                    {if $RECORD_CURRENCY_RATE eq ''}
                        {assign var="RECORD_CURRENCY_RATE" value=$SELECTED_CURRENCY.conversionrate}
                    {/if}
                    <input type="hidden" name="conversion_rate" id="conversion_rate" value="{$RECORD_CURRENCY_RATE}" />
                    <input type="hidden" value="{$SELECTED_CURRENCY.currency_id}" id="prev_selected_currency_id" />
                    <!-- TODO : To get default currency in even better way than depending on first element -->
                    <input type="hidden" id="default_currency_id" value="{$CURRENCIES.0.curid}" />
                </div>
            </th>
        </tr>
        <tr>
            <td><strong>{vtranslate('LBL_TOOLS',$MODULE)}</strong></td>
            <td><span class="redColor">*</span><strong>{vtranslate('LBL_ITEM_NAME',$MODULE)}</strong></td>
            <td><strong>{vtranslate('LBL_QTY',$MODULE)}</strong></td>
            <td><strong>{vtranslate('LBL_UNIT',$MODULE)}</strong></td>
            <td><strong>{vtranslate('LBL_LIST_PRICE',$MODULE)}</strong></td>
			<td><strong>{vtranslate('LBL_RBH',$MODULE)}</strong></td>
            <td><strong class="pull-right">{vtranslate('LBL_TOTAL',$MODULE)}</strong></td>
			<td></td>
        </tr>
        <tr id="row0" class="hide lineItemCloneCopy noValidate">
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
    <div class="verticalBottomSpacing">
        <div>
            {if $PRODUCT_ACTIVE eq 'true' && $SERVICE_ACTIVE eq 'true'}
                <div class="btn-toolbar">
                    <span class="btn-group">
                        <button type="button" class="btn btn-default addButton" id="addProduct">
                            <span class="glyphicon glyphicon-plus icon-white"></span><strong>{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
                        </button>
                    </span>
                    <span class="btn-group">
                        <button type="button" class="btn btn-default addButton" id="addService">
                            <span class="glyphicon glyphicon-plus icon-white"></span><strong>{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
                        </button>
                    </span>
                </div>
            {elseif $PRODUCT_ACTIVE eq 'true'}
                <div class="btn-group">
                    <button type="button" class="btn btn-default addButton" id="addProduct">
                        <span class="glyphicon glyphicon-plus icon-white"></span><strong> {vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
                    </button>
                </div>
            {elseif $SERVICE_ACTIVE eq 'true'}
                <div class="btn-group">
                    <button type="button" class="btn btn-default addButton" id="addService">
                        <span class="glyphicon glyphicon-plus icon-white"></span><strong> {vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
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
