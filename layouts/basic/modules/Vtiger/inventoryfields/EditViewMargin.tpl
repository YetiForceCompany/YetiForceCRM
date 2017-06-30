{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<input name="margin{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" type="text" class="margin form-control input-sm" readonly="readonly"/>
{/strip}
