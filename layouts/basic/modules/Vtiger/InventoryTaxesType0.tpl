{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if count($GLOBAL_TAXES) > 0}
		<div class="card js-panel mb-2">
			<div class="card-header py-1">
				<strong>{\App\Language::translate('LBL_GLOBAL_TAXS', $MODULE)}</strong>
				<div class="float-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="global" class="activeCheckbox">
				</div>
			</div>
			<div class="card-body js-panel__body d-none" data-js="class: d-none">
				<select class="select2 globalTax" name="globalTax" data-validation-engine="validate[required]">
					{foreach item=ITEM key=NAME from=$GLOBAL_TAXES}
						<option value="{CurrencyField::convertToUserFormat($ITEM.value, null, true)}">
							{App\Fields\Double::formatToDisplay($ITEM.value)}
							% - {\App\Language::translate($ITEM.name, $MODULE)}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
	{/if}
{/strip}
