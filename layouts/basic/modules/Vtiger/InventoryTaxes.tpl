{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-InventoryTaxes -->
	{assign var=AGGREGATION value=$CONFIG['aggregation']}
	<div class="modelContainer modal fade">
		<div class="modal-dialog">
			<form class="modal-content">
				<div class="modal-header align-items-center">
					<span class="mr-2 small">
						<span class="fas fa-long-arrow-alt-up"></span>
						<span class="fas fa-percent"></span>
					</span>
					<h5 class="modal-title">{\App\Language::translate('LBL_SELECT_TAX', $MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<input type="hidden" class="taxsType" value="{$AGGREGATION_TYPE}" />
					{foreach item=TAXID from=$CONFIG['taxs']}
						{assign var="TAX_TYPE_TPL" value="InventoryTaxesType"|cat:$TAXID|cat:".tpl"}
						{include file=\App\Layout::getTemplatePath($TAX_TYPE_TPL, $MODULE)}
					{/foreach}
					<hr />
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_TAX_VALUE', $MODULE)}:</div>
						<div class="col-md-6 text-right">
							<strong><span class="taxValue js-tax-value">0</span> %</strong>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_PRICE_BEFORE_TAX', $MODULE)}:</div>
						<div class="col-md-6 text-right">
							<strong><span class="valueNetPrice">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL} </strong>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_TAX_IN_TOTAL', $MODULE)}:</div>
						<div class="col-md-6 text-right">
							<strong><span class="valueTax" data-js="text">0</span> {$CURRENCY_SYMBOL} </strong>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_PRICE_AFTER_TAX', $MODULE)}:</div>
						<div class="col-md-6 text-right">
							<strong><span class="valuePrices">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</span></strong>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success js-save-taxs" type="button" data-js="click">
						<strong>
							<span class=" fas fa-check mr-2"></span>
							{\App\Language::translate('LBL_SAVE', $MODULE)}
						</strong>
					</button>
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
	<!-- /tpl-Base-InventoryTaxes -->
{/strip}
