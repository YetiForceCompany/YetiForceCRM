{strip}
	{assign var="SUPMODULE" value='Supplies'}
	{assign var="AGGREGATION" value=$CONFIG['aggregation']}
	<div class="modelContainer modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
					<h3 class="modal-title">{vtranslate('LBL_SELECT_TAX', $SUPMODULE)} {vtranslate($SINGLE_MODULE, $SUPMODULE)}</h3>
				</div>
				<div class="modal-body">
					<input type="hidden" class="taxsType" value="{$AGGREGATION_TYPE}" />
					{foreach item=TAXID from=$CONFIG['taxs']}
						{assign var="TAX_TYPE_TPL" value="TaxsType"|cat:$TAXID|cat:".tpl"}
						{include file=$TAX_TYPE_TPL|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl($TAX_TYPE_TPL,$MODULE)}
					{/foreach}
					<hr/>
					<div class="row">
						<div class="col-md-6">{vtranslate('LBL_PRICE_BEFORE_TAX', $SUPMODULE)}:</div>
						<div class="col-md-6 text-right"><strong>{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)} {$CURRENCY_SYMBOL}</strong></div>
					</div>
					<div class="row">
						<div class="col-md-6">{vtranslate('LBL_TAX_IN_TOTAL', $SUPMODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valueTax">0</span> {$CURRENCY_SYMBOL}</strong></div>
					</div>
					<div class="row">
						<div class="col-md-6">{vtranslate('LBL_PRICE_AFTER_TAX', $SUPMODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valuePrices">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</span></strong></div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success saveTaxs" type="submit"><strong>{vtranslate('LBL_SAVE', $SUPMODULE)}</strong></button>
					<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CANCEL', $SUPMODULE)}</strong></button>
				</div>
			</div>
		</div>
	</div>
{/strip}
