{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Detail-Widget-ProductsServicesConfig modal fade" tabindex="-1">
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
										<label class="col-form-label">{\App\Language::translate('Label', $QUALIFIED_MODULE)}: </label>
									</div>
									<div class="col-md-7">
										<input name="label" class="form-control" type="text"
											data-validation-engine="validate[required]"
											value="{$WIDGETINFO['label']}" />
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-4">
										<label class="col-form-label">{\App\Language::translate('LBL_SET_AS_DEFAULT', $QUALIFIED_MODULE)}: </label>
									</div>
									<div class="col-md-7">
										<select name="filter" class="select2 form-control marginLeftZero columnsSelect">
											{foreach from=Products_SummaryWidget_Model::MODULES item=MODULE}
												<option {if isset($WIDGETINFO['data']['filter']) && ($MODULE eq $WIDGETINFO['data']['filter'])}selected="selected" {/if}
													value="{$MODULE}">{\App\Language::translate($MODULE, $MODULE)}
												</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-4">
										<label class="col-form-label">
											{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}
											<a href="#" class="js-help-info" title="" data-placement="top"
												data-content="{\App\Language::translate('Limit entries info', $QUALIFIED_MODULE)}"
												data-original-title="{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}"><i
													class="fas fa-info-circle"></i></a>:
										</label>
									</div>
									<div class="col-md-2">
										<input name="limit" class="form-control" type="text"
											data-validation-engine="validate[required,custom[integer],min[1]]"
											value="{$WIDGETINFO['data']['limit']}" />
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
