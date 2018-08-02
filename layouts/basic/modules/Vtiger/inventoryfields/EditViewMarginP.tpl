{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div class="input-group input-group-sm">
		<input name="marginp{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" type="text" class="marginp form-control form-control-sm" readonly="readonly" />
		<div class="input-group-append">
			<span class="input-group-text">%</span>
		</div>
	</div>
{/strip}
