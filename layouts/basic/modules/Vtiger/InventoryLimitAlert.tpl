{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_LIMIT_TITLE', $MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_CREDIT_LIMIT', $MODULE)}:</div>
						<div class="col-md-6">
							<strong>{$LIMIT}</strong>
							{if $LIMIT != '-'}
								&nbsp;{$SYMBOL}
							{/if}
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_BALANCE_LIMIT', $MODULE)}:</div>
						<div class="col-md-6"><strong>{$BALANCE}</strong> {$SYMBOL}</div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_CURRENT_VALUE', $MODULE)}:</div>
						<div class="col-md-6"><strong>{$PRICE}</strong> {$SYMBOL}</div>
					</div>
					<hr />
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_TOTALS_LIMIT', $MODULE)}:</div>
						<div class="col-md-6"><strong>{$TOTALS}</strong> {$SYMBOL}</div>
					</div>
					<br />
					<div class="alert alert-danger marginbottomZero">{\App\Language::translate('LBL_LIMIT_ALERT', $MODULE)}</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
