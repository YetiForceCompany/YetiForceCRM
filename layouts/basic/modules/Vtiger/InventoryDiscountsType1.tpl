{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-InventoryDiscountsType1 -->
	{if $GROUP_DISCOUNT != 0}
		<div class="card js-panel mb-2" data-js="class: js-active">
			<div class="card-header py-1">
				<span class="yfm-Accounts mr-2"></span>
				<strong>{\App\Language::translate('LBL_ACCOUNT_DISCOUNT', $MODULE)}</strong>
				<div class="float-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="group" class="activeCheckbox">
				</div>
			</div>
			<div class="card-body js-panel__body d-none" data-js="class: d-none">
				<div>
					<p>
						{\App\Language::translate('LBL_DISCOUNT_FOR_ACCOUNT', $MODULE)}: {$ACCOUNT_NAME}
					</p>
					<div class="input-group">
						<span class="input-group-prepend">
							<div class="input-group-text">
								<input type="checkbox" name="groupCheckbox" value="on" class="groupCheckbox">
							</div>
						</span>
						<input type="text" class="form-control groupValue" name="groupDiscount" value="{App\Fields\Double::formatToDisplay($GROUP_DISCOUNT)}" readonly="readonly" data-validation-engine="validate[required]">
						<div class="input-group-append">
							<span class="input-group-text">%</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	{/if}
	<!-- /tpl-Base-InventoryDiscountsType1 -->
{/strip}
