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
	{assign var="deleted" value="deleted"|cat:$row_no}
    {assign var="hdnProductId" value="hdnProductId"|cat:$row_no}
    {assign var="productName" value="productName"|cat:$row_no}
    {assign var="comment" value="comment"|cat:$row_no}
    {assign var="productDescription" value="productDescription"|cat:$row_no}
    {assign var="qtyInStock" value="qtyInStock"|cat:$row_no}
    {assign var="qty" value="qty"|cat:$row_no}
    {assign var="usageUnit" value="usageUnit"|cat:$row_no}
	{assign var="rbh" value="rbh"|cat:$row_no}
    {assign var="listPrice" value="listPrice"|cat:$row_no}
	{assign var="purchase" value="purchase"|cat:$row_no}
	{assign var="margin" value="margin"|cat:$row_no}
	{assign var="marginp" value="marginp"|cat:$row_no}
    {assign var="productTotal" value="productTotal"|cat:$row_no}
    {assign var="subproduct_ids" value="subproduct_ids"|cat:$row_no}
    {assign var="subprod_names" value="subprod_names"|cat:$row_no}
    {assign var="entityIdentifier" value="entityType"|cat:$row_no}
    {assign var="entityType" value=$data.$entityIdentifier}
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}
	{assign var="productDeleted" value="productDeleted"|cat:$row_no}
	<td class="dragHandle">
		<span class="glyphicon glyphicon-trash deleteRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></span>
		&nbsp;<a><img src="{vimage_path('drag.png')}" border="0" alt="{vtranslate('LBL_DRAG',$MODULE)}"/></a>
		<input type="hidden" class="rowNumber" value="{$row_no}" />
	</td>
	<td>
		<!-- Product Re-Ordering Feature Code Addition Starts -->
		<input type="hidden" name="hidtax_row_no{$row_no}" id="hidtax_row_no{$row_no}" value="{$tax_row_no}"/>
		<!-- Product Re-Ordering Feature Code Addition ends -->
		<input type="hidden" id="{$hdnProductId}" name="{$hdnProductId}" value="{$data.$hdnProductId}" class="selectedModuleId"/>
		<input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$entityType}" class="lineItemType"/>
		<div class="input-group input-group-sm">
			<input type="text" id="{$productName}" name="{$productName}" value="{$data.$productName}" class="productName form-control input-sm {if $row_no neq 0} autoComplete {/if}" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}" data-validation-engine="validate[required]" {if !empty($data.$productName)} disabled="disabled" {/if}/>
			
			{if $row_no eq 0}
				<span class="input-group-addon"><img class="lineItemPopup cursorPointer alignMiddle" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" alt="{vtranslate('LBL_SERVICE', $MODULE)}" data-module-name="Services" data-field-name="serviceid" src="{vimage_path('Services.png')}"/></span>
				<span class="input-group-addon"><img class="lineItemPopup cursorPointer alignMiddle" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" alt="{vtranslate('LBL_PRODUCT', $MODULE)}" data-module-name="Products" data-field-name="productid" src="{vimage_path('Products.png')}"/></span>
				<span class="input-group-addon clearLineItem cursorPointer" title="{vtranslate('LBL_CLEAR',$MODULE)}" style="vertical-align:middle"><span class="glyphicon glyphicon-remove-sign"></span></span>
			{else}
				{if !$RECORD_ID} 
                                    {if ($entityType eq 'Services') and (!$data.$productDeleted) or $PRODUCT_ACTIVE neq 'true'} 
					<span class="input-group-addon"><img class="lineItemPopup cursorPointer alignMiddle" data-popup="ServicesPopup" data-module-name="Services" title="{vtranslate('Services',$MODULE)}" data-field-name="serviceid" src="{vimage_path('Services.png')}"/></span>
					<span class="input-group-addon"><span class="glyphicon glyphicon-remove-sign clearLineItem cursorPointer" title="{vtranslate('LBL_CLEAR',$MODULE)}" style="vertical-align:middle"></span></span>
                                    {elseif (!$data.$productDeleted)}
										<span class="input-group-addon"><img class="lineItemPopup cursorPointer alignMiddle" data-popup="ProductsPopup" data-module-name="Products" title="{vtranslate('Products',$MODULE)}" data-field-name="productid" src="{vimage_path('Products.png')}"/></span>
										<span class="input-group-addon clearLineItem cursorPointer" title="{vtranslate('LBL_CLEAR',$MODULE)}" style="vertical-align:middle"><span class="glyphicon glyphicon-remove-sign"></span></span>
                                    {/if}   
                                {else} 
                                    {if ($entityType eq 'Services') and (!$data.$productDeleted)} 
                                            <span class="input-group-addon"><img class="{if $SERVICE_ACTIVE}lineItemPopup{/if} cursorPointer alignMiddle" data-popup="ServicesPopup" data-module-name="Services" alt="{vtranslate('LBL_SERVICE', $MODULE)}" title="{vtranslate('Services',$MODULE)}" data-field-name="serviceid" src="{vimage_path('Services.png')}"/></span> 
                                           <span class="input-group-addon {if $SERVICE_ACTIVE}clearLineItem{/if} cursorPointer" title="{vtranslate('LBL_CLEAR',$MODULE)}" style="vertical-align:middle"><span class="glyphicon glyphicon-remove-sign"></span></span> 
                                    {elseif (!$data.$productDeleted)} 
                                            <span class="input-group-addon"><img class="{if $PRODUCT_ACTIVE}lineItemPopup{/if} cursorPointer alignMiddle" data-popup="ProductsPopup" data-module-name="Products" alt="{vtranslate('LBL_PRODUCT', $MODULE)}" title="{vtranslate('Products',$MODULE)}" data-field-name="productid" src="{vimage_path('Products.png')}"/></span> 
                                       <span class="input-group-addon {if $PRODUCT_ACTIVE}clearLineItem{/if} cursorPointer" title="{vtranslate('LBL_CLEAR',$MODULE)}" style="vertical-align:middle"><span class="glyphicon glyphicon-remove-sign"></span></span> 
                                    {/if}   
                                {/if} 
			{/if}
		</div>
		<input type="hidden" value="{$data.$subproduct_ids}" id="{$subproduct_ids}" name="{$subproduct_ids}" class="subProductIds" />
		<div id="{$subprod_names}" name="{$subprod_names}" class="subInformation"><span class="subProductsContainer">{$data.$subprod_names}</span></div>
		{if $data.$productDeleted}
			<div class="row deletedItem redColor">
				{if empty($data.$productName)}
					{vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$MODULE_NAME)}
				{else}
					{vtranslate('LBL_THIS',$MODULE_NAME)} {vtranslate($entityType)} {vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$MODULE_NAME)}
				{/if}
			</div>
		{else}
			
		{/if}
	</td>
	<td>
		<input id="{$qty}" name="{$qty}" type="text" class="qty smallInputBox form-control input-sm" data-validation-engine="validate[required,funcCall[Vtiger_GreaterThanZero_Validator_Js.invokeValidation]]" value="{if !empty($data.$qty)}{$data.$qty}{else}1{/if}" title="{if !empty($data.$qty)}{$data.$qty}{else}1{/if}"/>
	</td>
	<td>
		<span id="{$usageUnit}" class="usageUnit">{vtranslate($data.$usageUnit, $entityType)}</span>
	</td>
	<td>
		<div>
			<input id="{$listPrice}" name="{$listPrice}" value="{if !empty($data.$listPrice)}{$data.$listPrice}{else}0{/if}" title="{if !empty($data.$listPrice)}{$data.$listPrice}{else}0{/if}" type="text" data-validation-engine="validate[required,funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" class="listPrice smallInputBox form-control input-sm" />
			{vtranslate('LBL_PURCHASE',$MODULE)}:
			<input id="{$purchase}" name="{$purchase}" value="{if !empty($data.$purchase)}{$data.$purchase}{else}0{/if}" title="{if !empty($data.$purchase)}{$data.$purchase}{else}0{/if}" type="text" data-validation-engine="validate[required,funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" class="purchase smallInputBox form-control input-sm" />
			{vtranslate('LBL_MARGIN',$MODULE)}:
			<input id="{$margin}" name="{$margin}" value="{if !empty($data.$margin)}{$data.$margin}{else}0{/if}" title="{if !empty($data.$margin)}{$data.$margin}{else}0{/if}" type="text" data-validation-engine="validate[required,funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" class="margin smallInputBox form-control input-sm" readonly="readonly"/>
			{vtranslate('LBL_MARGINP',$MODULE)}:
			<input id="{$marginp}" name="{$marginp}" value="{if !empty($data.$marginp)}{$data.$marginp}{else}0{/if}" title="{if !empty($data.$marginp)}{$data.$marginp}{else}0{/if}" type="text" data-validation-engine="validate[required,funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" class="marginp smallInputBox form-control input-sm" readonly="readonly"/>
		</div>
	</td>
	<td>
		<input id="{$rbh}" name="{$rbh}" type="text" class="rbh smallInputBox form-control input-sm" value="{if !empty($data.$rbh)}{$data.$rbh}{else}0{/if}" title="{if !empty($data.$rbh)}{$data.$rbh}{else}0{/if}"/>
	</td>
	<td>
		<div id="productTotal{$row_no}" align="right" class="productTotal">{if $data.$productTotal}{$data.$productTotal}{else}0{/if}</div>
	</td>
	<td>
		<div style="width: 450px;"><textarea id="{$comment}" name="{$comment}" title="{vtranslate("Item Comment")}" class="lineItemCommentBox {if $row_no neq 0}ckEditorSource{/if} ckEditorBasic ckEditorSmall">{$data.$comment}</textarea></div>
	</td>	
