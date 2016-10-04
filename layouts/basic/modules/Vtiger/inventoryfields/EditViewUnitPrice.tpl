{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div class="input-group input-group-sm">
		<input name="{$FIELD->getColumnName()}{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" title="{$FIELD->getEditValue($VALUE)}" type="text" 
			   data-validation-engine="validate[required,funcCall[Vtiger_NumberUserFormat_Validator_Js.invokeValidation]]" 
			   class="unitPrice smallInputBox form-control input-sm" list-info="" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}/>

		{assign var=PRICEBOOK_MODULE_MODEL value=Vtiger_Module_Model::getInstance('PriceBooks')}
		{if $PRICEBOOK_MODULE_MODEL->isPermitted('DetailView')}
			<span class="input-group-addon priceBookPopup cursorPointer">
				<span class="userIcon-PriceBooks"  data-popup="Popup" data-module-name="PriceBooks" alt="{vtranslate('PriceBooks',$MODULE)}" title="{vtranslate('PriceBooks',$MODULE)}"/></span>
			</span>
		{/if}
	</div>
{/strip}
