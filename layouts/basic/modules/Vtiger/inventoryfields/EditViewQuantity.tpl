{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewQuantity -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{assign var=VALIDATION_ENGINE value='validate[required,funcCall[Vtiger_NumberUserFormat_Validator_Js.invokeValidation]]'}
	<div class="input-group input-group-sm">
		<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" type="text"
			   class="qty smallInputBox form-control form-control-sm" data-maximumlength="{$FIELD->getRangeValues()}"
			   data-validation-engine="{$VALIDATION_ENGINE}" value="{$FIELD->getEditValue($VALUE)}"
			   title="{$FIELD->getEditValue($VALUE)}" {if $FIELD->isReadOnly()}readonly="readonly"{/if}/>
		{assign var=QTY_PARAM value=''}
		{if isset($ITEM_DATA['name']) && ($REFERENCE_MODULE === 'Products' ||  $REFERENCE_MODULE === 'Services')}
			{assign var=REFERENCE_RECORD value=Vtiger_Record_Model::getInstanceById($ITEM_DATA['name'], $REFERENCE_MODULE)}
			{if $REFERENCE_RECORD && $REFERENCE_RECORD->has('qty_per_unit')}
				{assign var=QTY_PARAM value=$REFERENCE_RECORD->getDisplayValue('qty_per_unit')}
				{if in_array($REFERENCE_RECORD->getDisplayValue('usageunit'),['pcs','pack'])}
					{assign var=VALIDATION_ENGINE value='validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]'}
				{/if}
			{/if}
		{/if}
		<div class="input-group-append">
			<span class="input-group-text js-popover-tooltip qtyParamInfo {if !$QTY_PARAM}d-none{/if}"
				  data-js="popover" data-content="{$QTY_PARAM}"
				  title="{\App\Language::translate('Qty/Unit','Products')}">
				<i class="fas fa-info-circle"></i>
			</span>
		</div>
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewQuantity -->
{/strip}
