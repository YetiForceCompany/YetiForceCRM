{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{if $ITEM_DATA['unit'] === 'pack' || $ITEM_DATA['unit'] === 'pcs'}
		{assign var=VALIDATION_ENGINE value='validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]'}
	{else}
		{assign var=VALIDATION_ENGINE value='validate[required,funcCall[Vtiger_NumberUserFormat_Validator_Js.invokeValidation]]'}
	{/if}
	<div class="input-group input-group-sm">
		<input name="{$FIELD->getColumnName()}{$ROW_NO}" type="text" class="qty smallInputBox form-control input-sm" data-validation-engine="{$VALIDATION_ENGINE}" value="{$FIELD->getEditValue($VALUE)}" title="{$FIELD->getEditValue($VALUE)}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}/>
		<span class="input-group-btn">
			<button class="btn btn-default qtyparamButton{if $ITEM_DATA['qtyparam']} active{/if}{if $ITEM_DATA['unit'] !== 'pack'} hidden{/if}" data-rownum="{$ROW_NO}" type="button">{vtranslate('pcs','Products')}</button>
		</span>
	</div>
	<input type="checkbox" name="qtyparam{$ROW_NO}" value="1" class="qtyparam hidden" {if $ITEM_DATA['qtyparam']} checked{/if} />
{/strip}
