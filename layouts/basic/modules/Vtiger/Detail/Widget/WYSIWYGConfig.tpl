{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Detail-Widget-WYSIWYGConfig modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget">
					{if !empty($WID)}<input type="hidden" name="wid" value="{$WID}" />{/if}
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<h5 id="massEditHeader"
							class="modal-title">{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}</h5>
						<button type="button" data-dismiss="modal" class="close"
							title="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">
							&times;
						</button>
					</div>
					<div class="modal-body">
						<div class="modal-Fields">
							<div class="form-horizontal">
								<div class="form-group row">
									<div class="col-md-4">
										<strong>{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}</strong>:
									</div>
									<div class="col-md-7">
										{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-4">
										<label class="col-form-label">
											{\App\Language::translate('Label', $QUALIFIED_MODULE)}:
										</label>
									</div>
									<div class="col-md-7">
										<input name="label" class="form-control" type="text"
											data-validation-engine="validate[required]"
											value="{$WIDGETINFO['label']}" />
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-4">
										<label class="col-form-label">
											{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}:
										</label>
									</div>
									<div class="col-md-7">
										<select name="field_name" class="select2 form-control">
											{foreach from=$MODULE_MODEL->getWYSIWYGFields($SOURCE,$SOURCEMODULE) item=item key=key}
												<option {if isset($WIDGETINFO['data']['field_name']) && $WIDGETINFO['data']['field_name'] == $key}selected{/if}
													value="{$key}">{$item}</option>
											{/foreach}
										</select>
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
{/strip}
