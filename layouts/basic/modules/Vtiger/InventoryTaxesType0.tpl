{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if count($GLOBAL_TAXES) > 0}
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>{\App\Language::translate('LBL_GLOBAL_TAXS', $MODULE)}</strong>
				<div class="float-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="global" class="activeCheckbox">
				</div>
			</div>
			<div class="panel-body" style="display: none;">
				<select class="select2 globalTax" name="globalTax">
					{foreach item=ITEM key=NAME from=$GLOBAL_TAXES}
						<option value="{CurrencyField::convertToUserFormat($ITEM.value, null, true)}">
							{$ITEM.value}% - {\App\Language::translate($ITEM.name, $MODULE)}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
	{/if}
{/strip}
