{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-InventoryDiscountsType0 -->
	{if count($GLOBAL_DISCOUNTS) > 0}
		<div class="card js-panel mb-2" data-js="class: js-active">
			<div class="card-header py-1">
				<span class="adminIcon-discount-base mr-2"></span>
				<strong>{\App\Language::translate('LBL_AVAILABLE_DICOUNTS', $MODULE)}</strong>
				<div class="float-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="global" class="activeCheckbox">
				</div>
			</div>
			<div class="card-body js-panel__body d-none" data-js="class: d-none">
				<select class="select2 globalDiscount" name="globalDiscount" data-validation-engine="validate[required]">
					{foreach item=ITEM key=NAME from=$GLOBAL_DISCOUNTS}
						<option value="{CurrencyField::convertToUserFormat($ITEM.value, null, true)}">
							{App\Fields\Double::formatToDisplay($ITEM.value)}% - {\App\Language::translate($ITEM.name, $MODULE)}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
	{/if}
	<!-- /tpl-Base-InventoryDiscountsType0 -->
{/strip}
