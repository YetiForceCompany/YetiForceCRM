{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-InventoryDiscounts -->
	<div class="modelContainer modal fade">
		<div class="modal-dialog">
			<form class="modal-content">
				<div class="modal-header align-items-center">
					<span class="mr-2 small">
						<span class="fas fa-long-arrow-alt-down"></span>
						<span class="fas fa-percent"></span>
					</span>
					<h5 class="modal-title">{\App\Language::translate('LBL_SELECT_DISCOUNT', $MODULE_NAME)} {\App\Language::translate("SINGLE_{$MODULE_NAME}", $MODULE_NAME)}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<input type="hidden" class="aggregationType" value="{$AGGREGATION_TYPE}" />
					<input type="hidden" class="discountMode" value="{$DISCOUNT_MODE}" />
					{assign var="DISCOUNTS_EXISTS" value=count($GLOBAL_DISCOUNTS) || !empty($ACCOUNT_DISCOUNT) || array_intersect([2,3], $CONFIG['discounts'])}
					{if !$DISCOUNTS_EXISTS}
						<div class="alert alert-danger" role="alert">
							{\App\Language::translate('LBL_NO_DISCOUNTS')}
						</div>
					{else}
						<div class="form-group">
							<div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
								<label class="btn btn-sm btn-outline-primary{if !$IS_MARKUP} active{/if}">
									<input class="js-inv--discount-type" type="radio" name="discount-type" id="discont-type-0"
										autocomplete="off"
										{if !$IS_MARKUP}checked{/if}> {\App\Language::translate('Discont',$MODULE_NAME)}
								</label>
								<label class="btn btn-sm btn-outline-primary{if $IS_MARKUP} active{/if}">
									<input class="js-inv--discount-type markup" type="radio" name="discount-type" id="discont-type-1"
										autocomplete="off" {if $IS_MARKUP}checked{/if}> {\App\Language::translate('LBL_MARKUP',$MODULE_NAME)}
								</label>
							</div>
						</div>
						{foreach item=DISCOUNTID from=$CONFIG['discounts']}
							{assign var="PARAM_VALUE_NAME" value="{$DISCOUNT_MODEL->getAggregationNameById($DISCOUNTID)}Discount"}
							{assign var="DISCOUNT_VALUE" value=""}
							{if isset($DISCOUNT_PARAM[$PARAM_VALUE_NAME])}
								{assign var="DISCOUNT_VALUE" value=$DISCOUNT_PARAM[$PARAM_VALUE_NAME]}
							{/if}
							{assign var="DISCOUNT_TYPE_TPL" value="InventoryDiscountsType"|cat:$DISCOUNTID|cat:".tpl"}
							{include file=\App\Layout::getTemplatePath($DISCOUNT_TYPE_TPL, $MODULE) DISCOUNT_VALUE=$DISCOUNT_VALUE}
						{/foreach}
					{/if}
					<hr />
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_PRICE_BEFORE_DISCOUNT', $MODULE)}:</div>
						<div class="col-md-6 text-right">
							<strong><span class="valueTotalPrice">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</strong>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_DISCOUNT_IN_TOTAL', $MODULE)}:</div>
						<div class="col-md-6 text-right">
							<strong><span class="valueDiscount">0</span> {$CURRENCY_SYMBOL}</strong>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_PRICE_AFTER_DISCOUNT', $MODULE)}:</div>
						<div class="col-md-6 text-right">
							<strong><span class="valuePrices">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</span></strong>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					{if $DISCOUNTS_EXISTS}
						<button class="btn btn-success js-save-discount" type="button" data-js="click">
							<strong>
								<span class="fas fa-check mr-2"></span>
								{\App\Language::translate('LBL_SAVE', $MODULE)}
							</strong>
						</button>
					{/if}
					<button class="btn btn-danger" type="reset" data-dismiss="modal">
						<strong>
							<span class="fas fa-times mr-2"></span>
							{\App\Language::translate('LBL_CANCEL', $MODULE)}
						</strong>
					</button>
				</div>
			</form>
		</div>
	</div>
	<!-- /tpl-Base-InventoryDiscounts -->
{/strip}
