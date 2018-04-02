{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if count($GLOBAL_DISCOUNTS) > 0}
		<div class="card js-panel" data-js="class: js-active">
			<div class="card-header">
				<strong>{\App\Language::translate('LBL_GLOBAL_DISCOUNTS', $MODULE)}</strong>
				<div class="float-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="global" class="activeCheckbox">
				</div>
			</div>
			<div class="card-body js-panel__body d-none" data-js="class: d-none">
				<select class="select2 globalDiscount" name="globalDiscount">
					{foreach item=ITEM key=NAME from=$GLOBAL_DISCOUNTS}
						<option value="{CurrencyField::convertToUserFormat($ITEM.value, null, true)}">
							{$ITEM.value}% - {\App\Language::translate($ITEM.name, $MODULE)}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
	{/if}
{/strip}
