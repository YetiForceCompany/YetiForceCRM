{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<input name="margin{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" type="text" class="margin form-control input-sm" readonly="readonly"/>
{/strip}
