{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Detail-Widget-DetailViewConfig modal" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form class="form-modalAddWidget">
					{if !empty($WID)}<input type="hidden" name="wid" value="{$WID}" />{/if}
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<h5 id="massEditHeader" class="modal-title">
							{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}
						</h5>
						<button type="button" class="close" data-dismiss="modal"
							aria-label="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">
							<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-container-sm">
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">
									{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}:
								</label>
								<div class="col-md-7 form-control-plaintext">
									{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">
									{\App\Language::translate('Label', $QUALIFIED_MODULE)}:
								</label>
								<div class="col-md-7 py-1">
									<input name="label" class="form-control" type="text"
										data-validation-engine="validate[required]"
										value="{$WIDGETINFO['label']}" />
								</div>
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
