{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">{vtranslate('LBL_LIMIT_TITLE', $MODULE)}</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6">{vtranslate('LBL_CREDIT_LIMIT', $MODULE)}:</div>
						<div class="col-md-6">
							<strong>{$LIMIT}</strong>
							{if $LIMIT != '-'}
								&nbsp;{$SYMBOL}
							{/if}
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">{vtranslate('LBL_BALANCE_LIMIT', $MODULE)}:</div>
						<div class="col-md-6"><strong>{$BALANCE}</strong> {$SYMBOL}</div>
					</div>
					<div class="row">
						<div class="col-md-6">{vtranslate('LBL_CURRENT_VALUE', $MODULE)}:</div>
						<div class="col-md-6"><strong>{$PRICE}</strong> {$SYMBOL}</div>
					</div>
					<hr/>
					<div class="row">
						<div class="col-md-6">{vtranslate('LBL_TOTALS_LIMIT', $MODULE)}:</div>
						<div class="col-md-6"><strong>{$TOTALS}</strong> {$SYMBOL}</div>
					</div>
					<br/>
					<div class="alert alert-danger marginbottomZero">{vtranslate('LBL_LIMIT_ALERT', $MODULE)}</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
