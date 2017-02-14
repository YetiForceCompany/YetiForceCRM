{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if count($GLOBAL_DISCOUNTS) > 0}
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>{vtranslate('LBL_GLOBAL_DISCOUNTS', $MODULE)}</strong>
				<div class="pull-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="global" class="activeCheckbox">
				</div>
			</div>
			<div class="panel-body" style="display: none;">
				<select class="select2 globalDiscount" name="globalDiscount">
					{foreach item=ITEM key=NAME from=$GLOBAL_DISCOUNTS}
						<option value="{CurrencyField::convertToUserFormat($ITEM.value, null, true)}">
							{$ITEM.value}% - {vtranslate($ITEM.name, $MODULE)}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
	{/if}
{/strip}
