
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
    <!--
    All final details are stored in the first element in the array with the index name as final_details
    so we will get that array, parse that array and fill the details
    -->
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}

    {assign var="IS_INDIVIDUAL_TAX_TYPE" value=false}
    {assign var="IS_GROUP_TAX_TYPE" value=true}

    {if $FINAL.taxtype eq 'individual'}
        {assign var="IS_GROUP_TAX_TYPE" value=false}
        {assign var="IS_INDIVIDUAL_TAX_TYPE" value=true}
    {/if}

    <input type="hidden" class="numberOfCurrencyDecimal" value="{$USER_MODEL->get('no_of_currency_decimals')}" />

    <table class="table table-bordered blockContainer lineItemTable" id="lineItemTab">
        <tr>
            <th colspan="2"><span class="inventoryLineItemHeader"><h4>{vtranslate('LBL_ITEM_DETAILS', $MODULE)}</h4></span></th>
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
	<th colspan="3" class="chznDropDown">
	<div class="">
		<div class="inventoryLineItemHeader">
			<span class="alignTop">{vtranslate('LBL_TAX_MODE', $MODULE)}</span>
		</div>
		<select class="chzn-select lineItemTax" id="taxtype" name="taxtype" title="{vtranslate('LBL_TAX_MODE', $MODULE)}" style="width: 164px;">
			<OPTION value="individual" {if $IS_INDIVIDUAL_TAX_TYPE}selected{/if}>{vtranslate('LBL_INDIVIDUAL', $MODULE)}</OPTION>
			<OPTION value="group" {if $IS_GROUP_TAX_TYPE}selected{/if}>{vtranslate('LBL_GROUP', $MODULE)}</OPTION>
		</select>
	</div>
</th>
</tr>
<tr>
	<td><strong>{vtranslate('LBL_TOOLS',$MODULE)}</strong></td>
	<td><span class="redColor">*</span><strong>{vtranslate('LBL_ITEM_NAME',$MODULE)}</strong></td>
	<td><strong>{vtranslate('LBL_QTY',$MODULE)}</strong></td>
	<td><strong>{vtranslate('LBL_UNIT',$MODULE)}</strong></td>
	<td><strong>{vtranslate('LBL_LIST_PRICE',$MODULE)}</strong></td>
	<td><strong class="pull-right">{vtranslate('LBL_TOTAL',$MODULE)}</strong></td>
	<td><strong class="pull-right">{vtranslate('LBL_NET_PRICE',$MODULE)}</strong></td>
</tr>
<tr id="row0" class="hide lineItemCloneCopy noValidate">
	{include file="LineItemsContent.tpl"|@vtemplate_path:$MODULE row_no=0 data=[]}
</tr>
{foreach key=row_no item=data from=$RELATED_PRODUCTS}
	<tr id="row{$row_no}" class="lineItemRow" {if $data["entityType$row_no"] eq 'Products'}data-quantity-in-stock={$data["qtyInStock$row_no"]}{/if}>
		{include file="LineItemsContent.tpl"|@vtemplate_path:$MODULE row_no=$row_no data=$data}
	</tr>
{/foreach}
{if count($RELATED_PRODUCTS) eq 0}
	<tr id="row1" class="lineItemRow">
		{include file="LineItemsContent.tpl"|@vtemplate_path:$MODULE row_no=1 data=[]}
	</tr>
{/if}

</table>


<div class="verticalBottomSpacing">
	<div>
		{if $PRODUCT_ACTIVE eq 'true' && $SERVICE_ACTIVE eq 'true'}
			<div class="btn-toolbar">
				<span class="btn-group">
					<button type="button" class="btn btn-default addButton" id="addProduct">
						<span class="glyphicon glyphicon-plus"></span><strong>{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
					</button>
				</span>
				<span class="btn-group">
					<button type="button" class="btn btn-default addButton" id="addService">
						<span class="glyphicon glyphicon-plus"></span><strong>{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
					</button>
				</span>
			</div>
		{elseif $PRODUCT_ACTIVE eq 'true'}
			<div class="btn-group">
				<button type="button" class="btn btn-default addButton" id="addProduct">
					<span class="glyphicon glyphicon-plus"></span><strong> {vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
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
<div class="table-responsive"> 
    <table class="table table-bordered blockContainer lineItemTable" id="lineItemResult">
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
        <tr>
            <td  width="83%">
                <div class="pull-right"><strong>{vtranslate('LBL_ITEMS_TOTAL',$MODULE)}</strong></div>
            </td>
            <td>
                <div id="netTotal" class="pull-right netTotal">{if !empty($FINAL.hdnSubTotal)}{$FINAL.hdnSubTotal}{else}0{/if}</div>
            </td>
        </tr>
        <tr>
            <td width="83%">
                <span class="pull-right">(-)&nbsp;<strong><a href="javascript:void(0)"  id="finalDiscount">{vtranslate('LBL_TOTAL_DISCOUNT',$MODULE)}</a></strong></span>
            </td>
            <td>
                <span id="discountTotal_final" class="pull-right discountTotal_final">{if $FINAL.discountTotal_final}{$FINAL.discountTotal_final}{else}0{/if}</span>

                <!-- Popup Discount Div -->
                <div id="finalDiscountUI" class="finalDiscountUI validCheck hide">
                    {assign var=DISCOUNT_TYPE_FINAL value="zero"}
                    {if !empty($FINAL.discount_type_final)}
                        {assign var=DISCOUNT_TYPE_FINAL value=$FINAL.discount_type_final }
                    {/if}
                    <input type="hidden" id="discount_type_final" class="form-control" name="discount_type_final" value="{$DISCOUNT_TYPE_FINAL}" title="{$DISCOUNT_TYPE_FINAL}" />
                    <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table popupTable">
                        <thead>
                            <tr>
                                <th id="discount_div_title_final"><strong>{vtranslate('LBL_SET_DISCOUNT_FOR',$MODULE)}:{$data.$productTotal}</strong>
                                    <button type="button" class="close closeDiv">x</button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="radio" name="discount_final" class="finalDiscounts" title="{vtranslate('LBL_ZERO_DISCOUNT',$MODULE)}" data-discount-type="zero" {if $DISCOUNT_TYPE_FINAL eq 'zero'}checked{/if} />&nbsp; {vtranslate('LBL_ZERO_DISCOUNT',$MODULE)}
                                    <!-- Make the discount value as zero -->
                                    <input type="hidden" class="discountVal" value="0" />
								</td>
                            </tr>
                            <tr>
                                <td>
									<div class="col-md-6 paddingLRZero">
										<input type="radio" name="discount_final" class="finalDiscounts" title="{vtranslate('LBL_OF_PRICE',$MODULE)}" data-discount-type="percentage" {if $DISCOUNT_TYPE_FINAL eq 'percentage'}checked{/if} />&nbsp; % {vtranslate('LBL_OF_PRICE',$MODULE)}
									</div>	
									<div class="input-group {if $DISCOUNT_TYPE_FINAL neq 'percentage'}hide{/if}"><input type="text" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" id="discount_percentage_final" name="discount_percentage_final" value="{$FINAL.discount_percentage_final}" title="{$FINAL.discount_percentage_final}" class="discount_percentage_final smallInputBox form-control input-sm pull-right discountVal " />
										<span class="input-group-addon">%</span></div>
								</td>
                            </tr>
                            <tr>
                                <td><input type="radio" name="discount_final" class="finalDiscounts" data-discount-type="amount" title="{vtranslate('LBL_DIRECT_PRICE_REDUCTION',$MODULE)}" {if $DISCOUNT_TYPE_FINAL eq 'amount'}checked{/if} />&nbsp;{vtranslate('LBL_DIRECT_PRICE_REDUCTION',$MODULE)}
									<input type="text" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]"  id="discount_amount_final" name="discount_amount_final" value="{$FINAL.discount_amount_final}" title="{$FINAL.discount_amount_final}" class="smallInputBox form-control input-sm pull-right discount_amount_final discountVal {if $DISCOUNT_TYPE_FINAL neq 'amount'}hide{/if}" /></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer backgroundColor lineItemPopupModalFooter modal-footer-padding">
                        <div class=" pull-right cancelLinkContainer">
                            <a class="cancelLink btn btn-warning" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <button class="btn btn-success finalDiscountSave" type="button" name="lineItemActionSave"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                    </div>
                </div>
                <!-- End Popup Div -->
            </td>
        </tr>
		<tr>
			<td width="83%">
				<span class="pull-right"><strong>{vtranslate('LBL_PRE_TAX_TOTAL', $MODULE_NAME)} </strong></span>
			</td>
			<td>
				{assign var=PRE_TAX_TOTAL value=$FINAL.preTaxTotal}
				<span class="pull-right" id="preTaxTotal">{if $PRE_TAX_TOTAL}{$PRE_TAX_TOTAL}{else}0{/if}</span>
				<input type="hidden" id="pre_tax_total" name="pre_tax_total" title="{if $PRE_TAX_TOTAL}{$PRE_TAX_TOTAL}{else}0{/if}" value="{if $PRE_TAX_TOTAL}{$PRE_TAX_TOTAL}{else}0{/if}"/>
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
		<!-- Group Tax - starts -->
        <tr id="group_tax_row" valign="top" class="{if $IS_INDIVIDUAL_TAX_TYPE}hide{/if}">
            <td width="83%">
                <span class="pull-right">(+)&nbsp;<strong><a href="javascript:void(0)" id="finalTax">{vtranslate('LBL_TAX',$MODULE)}</a></strong></span>
                <!-- Pop Div For Group TAX -->
                <div class="hide finalTaxUI validCheck" id="group_tax_div">
                    <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
                        <tr>
                            <th id="group_tax_div_title" colspan="2" nowrap align="left" >{vtranslate('LBL_GROUP_TAX',$MODULE)}</th>
                            <th colspan="2" align="right">
                                <button type="button" class="close closeDiv">x</button>
                            </th>
                        </tr>
                        {foreach item=tax_detail name=group_tax_loop key=loop_count from=$TAXES}
                            <tr>
                                <td class="lineOnTop">
									<div class="input-group input-group-sm">
										<span class="input-group-addon">
											<input type="radio" aria-label="..." name="group_tax_option " class="group_tax_option" value="{$tax_detail.taxname}" title="{$tax_detail.taxname}" {if {$FINAL['tax']} == $tax_detail.taxname}checked{/if}>
										</span>
										<input type="text" size="5" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" name="{$tax_detail.taxname}_group_percentage" id="group_tax_percentage{$smarty.foreach.group_tax_loop.iteration}" value="{$tax_detail.percentage}" title="{$tax_detail.percentage}" class="smallInputBox form-control input-sm groupTaxPercentage" aria-label="..."/>
										<span class="input-group-addon">%</span>
									</div>
                                </td>
                                <td class="lineOnTop textAlignCenter"><div class="textOverflowEllipsis">{$tax_detail.taxlabel}</div></td>
                                <td class="lineOnTop">
                                    <input type="text" style="min-width: 55px;" size="6" name="{$tax_detail.taxname}_group_amount" id="group_tax_amount{$smarty.foreach.group_tax_loop.iteration}" style="cursor:pointer;" value="{$tax_detail.amount}" title="{$tax_detail.amount}" readonly class="cursorPointer smallInputBox form-control input-sm groupTaxTotal" />
                                </td>
                            </tr>
                        {/foreach}
                        <input type="hidden" id="group_tax_count" value="{$smarty.foreach.group_tax_loop.iteration}" />
                    </table>
                    <div class="modal-footer lineItemPopupModalFooter modal-footer-padding">
                        <div class=" pull-right cancelLinkContainer">
                            <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <button class="btn btn-success" type="button" name="lineItemActionSave"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                    </div>
                </div>
                <!-- End Popup Div Group Tax -->
            </td>
            <td><span id="tax_final" class="pull-right tax_final">{if $FINAL.tax_totalamount}{$FINAL.tax_totalamount}{else}0{/if}</span></td>
        </tr>
        <tr valign="top">
            <td  width="83%">
                <span class="pull-right"><strong>{vtranslate('LBL_GRAND_TOTAL',$MODULE)}</strong></span>
            </td>
            <td>
                <span id="grandTotal" name="grandTotal" class="pull-right grandTotal">{$FINAL.grandTotal}</span>
            </td>
        </tr>
        {if $MODULE eq 'Invoice' or $MODULE eq 'PurchaseOrder'}
            <tr valign="top">
                <td width="83%" >
                    <div class="pull-right">
                        {if $MODULE eq 'Invoice'}
                            <strong>{vtranslate('LBL_RECEIVED',$MODULE)}</strong>
                        {else}
                            <strong>{vtranslate('LBL_PAID',$MODULE)}</strong>
                        {/if}
                    </div>
                </td>
                <td>
                    {if $MODULE eq 'Invoice'}
						<span class="pull-right"><input id="received" name="received" type="text" class="lineItemInputBox form-control input-sm" value="{if $RECORD->getDisplayValue('received') && !($IS_DUPLICATE)}{$RECORD->getDisplayValue('received')}{else}0{/if}" title="{if $RECORD->getDisplayValue('received') && !($IS_DUPLICATE)}{$RECORD->getDisplayValue('received')}{else}0{/if}"></span>
						{else}
                        <span class="pull-right"><input id="paid" name="paid" type="text" class="lineItemInputBox form-control input-sm" value="{if $RECORD->getDisplayValue('paid') && !($IS_DUPLICATE)}{$RECORD->getDisplayValue('paid')}{else}0{/if}" title="{if $RECORD->getDisplayValue('paid') && !($IS_DUPLICATE)}{$RECORD->getDisplayValue('paid')}{else}0{/if}"></span>
						{/if}
                </td>
            </tr>
			<!--
            <tr valign="top">
                <td width="83%" >
                    <div class="pull-right">
                        <b>{vtranslate('LBL_BALANCE',$MODULE)}</b>
                    </div>
                </td>
                <td>
                    <span class="pull-right"><input id="balance" name="balance" type="text" class="lineItemInputBox form-control" value="{if $RECORD->getDisplayValue('balance') && !($IS_DUPLICATE)}{$RECORD->getDisplayValue('balance')}{else}0{/if}" readonly></span>
                </td>
            </tr>
			-->
        {/if}
    </table>
</div>
<br>
<input type="hidden" name="totalProductCount" id="totalProductCount" value="{$row_no}" />
<input type="hidden" name="subtotal" id="subtotal" value="" />
<input type="hidden" name="total" id="total" value="" />
{/strip}
