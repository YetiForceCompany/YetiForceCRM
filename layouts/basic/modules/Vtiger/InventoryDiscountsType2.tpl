{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-InventoryDiscountsType2 -->
	<div class="card js-panel mb-2" data-js="class: js-active">
		<div class="card-header py-1">
			<span class="fas fa-percent mr-2"></span>
			<strong>{\App\Language::translate('LBL_CUSTOM_DISCOUNT', $MODULE)}</strong>
			<div class="float-right">
				<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="individual" class="activeCheckbox">
			</div>
		</div>
		<div class=" card-body js-panel__body d-none" data-js="class: d-none">
			<div class="container-fluid">
				<div class="form-row">
					<div class="col-md-6 text-center">
						<div class="radio align-items-center">
							<input type="radio" name="individualDiscountType" value="percentage" class="individualDiscountType mr-2" data-symbol="%" checked>
							<label>
								{\App\Language::translate('LBL_PERCENTAGE_DISCOUNTS', $MODULE)}
							</label>
						</div>
					</div>
					{if $DISCOUNT_TYPE == '0'}
						<div class="col-md-6 text-center">
							<div class="radio align-items-center">
								<input type="radio" name="individualDiscountType" value="amount" class="individualDiscountType mr-2  ml-2" data-symbol="{$CURRENCY_SYMBOL}">
								<label>
									{\App\Language::translate('LBL_AMOUNT_DISCOUNTS', $MODULE)}
								</label>
							</div>
						</div>
					{/if}
				</div>
				<div class="row">
					<div class="input-group individualDiscountContainer">
						<input type="text" name="individualDiscount" class="form-control individualDiscountValue" value="0" data-validation-engine="validate[required]">
						<div class="input-group-append">
							<span class="input-group-text">%</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-InventoryDiscountsType2 -->
{/strip}
