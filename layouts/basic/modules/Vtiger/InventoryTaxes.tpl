{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="AGGREGATION" value=$CONFIG['aggregation']}
	<div class="modelContainer modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
					<h3 class="modal-title">{vtranslate('LBL_SELECT_TAX', $MODULE)} {vtranslate($SINGLE_MODULE, $MODULE)}</h3>
				</div>
				<div class="modal-body">
					<input type="hidden" class="taxsType" value="{$AGGREGATION_TYPE}" />
					{foreach item=TAXID from=$CONFIG['taxs']}
						{assign var="TAX_TYPE_TPL" value="InventoryTaxesType"|cat:$TAXID|cat:".tpl"}
						{include file=$TAX_TYPE_TPL|@vtemplate_path:$MODULE}
					{/foreach}
					<hr/>
					<div class="row">
						<div class="col-md-6">{vtranslate('LBL_PRICE_BEFORE_TAX', $MODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valueNetPrice">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</strong></div>
					</div>
					<div class="row">
						<div class="col-md-6">{vtranslate('LBL_TAX_IN_TOTAL', $MODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valueTax">0</span> {$CURRENCY_SYMBOL}</strong></div>
					</div>
					<div class="row">
						<div class="col-md-6">{vtranslate('LBL_PRICE_AFTER_TAX', $MODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valuePrices">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</span></strong></div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success saveTaxs" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
					<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CANCEL', $MODULE)}</strong></button>
				</div>
			</div>
		</div>
	</div>
{/strip}
