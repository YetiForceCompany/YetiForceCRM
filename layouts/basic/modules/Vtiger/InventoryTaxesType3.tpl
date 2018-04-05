{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $GROUP_TAXS}
		<div class="card js-panel" data-js="class: js-active">
			<div class="card-header">
				<strong>{\App\Language::translate('LBL_REGIONAL_TAX', $MODULE)}</strong>
				<div class="float-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="account" class="activeCheckbox">
				</div>
			</div>
			<div class="card-body js-panel__body d-none" data-js="class: d-none">
				<div>
					<p>
						{\App\Language::translate('LBL_TAX_FOR_ACCOUNT', $MODULE)}: {$ACCOUNT_NAME}
					</p>
					<select class="select2 regionalTax" name="regionalTax">
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
