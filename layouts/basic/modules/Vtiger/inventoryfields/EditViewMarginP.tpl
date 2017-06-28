{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div class="input-group input-group-sm">
		<input name="marginp{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" type="text" class="marginp form-control input-sm" readonly="readonly"/>
		<span class="input-group-addon cursorPointer">%</span>
	</div>
{/strip}
