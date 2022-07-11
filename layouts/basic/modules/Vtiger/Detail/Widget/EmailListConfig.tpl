{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-EmailListConfig -->
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget">
					{if !empty($WID)}<input type="hidden" name="wid" value="{$WID}" />{/if}
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<h5 id="massEditHeader"
							class="modal-title">{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}</h5>
						<button type="button" data-dismiss="modal" class="close"
							title="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">&times;
						</button>
					</div>
					<div class="modal-body">
						<div class="form-horizontal">
							<div class="form-container-sm">
								<div class="form-group form-group-sm row mb-3">
									<div class="col-md-4">
										{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}:
									</div>
									<div class="col-md-7">
										{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
									</div>
								</div>
								<div class="form-group form-group-sm row">
									<div class="col-md-4">
										<label class="col-form-label">{\App\Language::translate('Label', $QUALIFIED_MODULE)}:</label>
									</div>
									<div class="col-md-7">
										<input name="label" class="form-control" type="text" data-validation-engine="validate[required]" value="{$WIDGETINFO['label']}" />
									</div>
								</div>
								<div class="form-group form-group-sm form-switch-mini row">
									<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_CUSTOM_FILTER')}:</label>
									<div class="col-md-7 py-1">
										{assign var=SHOW_FILTER isset($WIDGETINFO['data']['filter']) && $WIDGETINFO['data']['filter'] == 1}
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-sm btn-outline-primary {if $SHOW_FILTER}active{/if}">
												<input type="radio" name="filter" id="option1" autocomplete="off" value="1" {if $SHOW_FILTER}checked{/if}>
												{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
											</label>
											<label class="btn btn-sm btn-outline-primary {if !$SHOW_FILTER}active{/if}">
												<input type="radio" name="filter" id="option2" autocomplete="off" value="0" {if !$SHOW_FILTER}checked{/if}>
												{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
											</label>
										</div>
									</div>
								</div>
								<div class="form-group form-group-sm row">
									<div class="col-md-4">
										<label class="col-form-label">
											{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}:
											<a href="#" class="js-help-info" title="" data-placement="top" data-content="{\App\Language::translate('Limit entries info', $QUALIFIED_MODULE)}"
												{' '}data-original-title="{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}">
												<i class="fas fa-info-circle"></i>
											</a>
										</label>
									</div>
									<div class="col-md-7">
										<input name="limit" class="form-control" type="text" data-validation-engine="validate[required,custom[integer],min[1]]" value="{$WIDGETINFO['data']['limit']}" />
									</div>
								</div>
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-EmailListConfig -->
{/strip}
