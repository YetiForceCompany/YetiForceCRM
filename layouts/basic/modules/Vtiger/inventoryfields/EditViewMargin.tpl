{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<input name="margin{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" type="text" class="margin form-control form-control-sm" readonly="readonly" />
{/strip}
