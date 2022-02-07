{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-InventoryBlockConfig -->
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget form-horizontal validateForm">
					{if !empty($WID)}<input type="hidden" name="wid" value="{$WID}" />{/if}
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<h5 id="massEditHeader" class="modal-title">
							<span class="fas fa-plus mr-1"></span>{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}
						</h5>
						<button type="button" class="close" data-dismiss="modal"
							aria-label="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-container-sm">
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}
									:</label>
								<div class="col-md-7 col-form-label">
									{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">
									{\App\Language::translate('Label', $QUALIFIED_MODULE)}:
								</label>
								<div class="col-md-7 py-1">
									<input name="label" class="form-control form-control-sm" data-validation-engine="validate[required]" type="text" value="{$WIDGETINFO['label']}" />
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_SELECTING_FIELDS', $QUALIFIED_MODULE)}:</label>
								<div class="col-md-7 py-1">
									<select name="relatedfields" multiple class="select2 form-control form-control-sm" data-validation-engine="validate[required]" data-select-cb="registerSelectSortable">
										{foreach from=Vtiger_Inventory_Model::getInstance($SOURCEMODULE)->getFields() key=FIELD_NAME item=FIELD_MODEL}
											{if $FIELD_MODEL->isVisibleInDetail()}
												<option value="{$FIELD_NAME}" {' '}
													{if !empty($WIDGETINFO['data']['relatedfields']) && in_array($FIELD_NAME, $WIDGETINFO['data']['relatedfields'])}
														selected="selected" data-sort-index="{array_search($FIELD_NAME, $WIDGETINFO['data']['relatedfields'])}"
													{/if} data-module="{$SOURCE}">
													{\App\Language::translate($FIELD_MODEL->get('label'), $SOURCEMODULE)}
												</option>
											{/if}
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top"
										data-content="{\App\Language::translate('Limit entries info', $QUALIFIED_MODULE)}"
										data-original-title="{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}"><i
											class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 py-1">
									<input name="limit" class="form-control form-control-sm" type="text"
										data-validation-engine="validate[required,custom[integer],min[1]]"
										value="{$WIDGETINFO['data']['limit']}" />
								</div>
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-InventoryBlockConfig -->
{/strip}
