{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="input-group input-group-sm">
		{if $SUP_VALUE == ''}
			{assign var="VALUE" value=$FIELD->get('defaultvalue')}
		{else}
			{assign var="VALUE" value=$SUP_VALUE}
		{/if}

		<input name="{$FIELD->getColumnName()}{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" title="{$FIELD->getEditValue($VALUE)}" type="text" 
			   data-validation-engine="validate[required,funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" 
			   class="listPrice smallInputBox form-control input-sm" list-info=""/>

		{assign var=PRICEBOOK_MODULE_MODEL value=Vtiger_Module_Model::getInstance('PriceBooks')}
		{if in_array('3',$DISCOUNTS_CONFIG['discounts'])  && $PRICEBOOK_MODULE_MODEL->isPermitted('DetailView')}
			<span class="input-group-addon">
				<img src="{vimage_path('PriceBooks.png')}" class="cursorPointer alignMiddle priceBookPopup" data-popup="Popup" data-module-name="PriceBooks" alt="{vtranslate('PriceBooks',$SUPMODULE)}" title="{vtranslate('PriceBooks',$SUPMODULE)}"/>
			</span>
		{/if}
	</div>
	<div>
		{assign var="TOTAL_PRICE" value=floatval($SUP_DATA['qty']) * floatval($SUP_DATA['price'])}
		{vtranslate('LBL_TOTAL_PRICE',$SUPMODULE)}: &nbsp;
		<span class="totalPriceText">{$TOTAL_PRICE}</span>
	</div>
{/strip}
