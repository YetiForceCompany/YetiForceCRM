{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $GROUP_TAXS}
		<div class="card js-panel  mb-2" data-js="class: js-active">
			<div class="card-header py-1">
				<strong>{\App\Language::translate('LBL_REGIONAL_TAX', $MODULE)}</strong>
				<div class="float-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="regional" class="activeCheckbox">
				</div>
			</div>
			<div class="card-body js-panel__body d-none" data-js="class: d-none">
				<div>
					<p>
						{\App\Language::translate('LBL_TAX_FOR_ACCOUNT', $MODULE)}: {$ACCOUNT_NAME}
					</p>
					<select class="select2 regionalTax" name="regionalTax" data-validation-engine="validate[required]">
						{foreach item=TAX from=$GROUP_TAXS}
							{assign var=VALUE value=CurrencyField::convertToUserFormat($TAX['value'], null, true)}
							<option value="{$VALUE}">
								{$VALUE}% - {\App\Language::translate({$TAX['name']}, $MODULE)}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	{/if}
{/strip}
