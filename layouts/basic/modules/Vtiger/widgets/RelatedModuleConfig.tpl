{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget form-horizontal validateForm">
					<input type="hidden" name="wid" value="{$WID}" />
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<button type="button" data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">×</button>
						<h3 id="massEditHeader" class="modal-title">{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}</h3>
					</div>
					<div class="modal-body">
						<div class="form-container-sm">
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}:</label>
								<div class="col-md-7 form-control-static">
									{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('Label', $QUALIFIED_MODULE)}:</label>
								<div class="col-md-7 controls"><input name="label" class="form-control" type="text" value="{$WIDGETINFO['label']}" /></div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('Related module', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Related module info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Related module', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls">
									<select name="relatedmodule" class="select2 form-control marginLeftZero" data-validation-engine="validate[required]">
										{foreach from=$RELATEDMODULES item=item key=key}
											<option value="{$item['related_tabid']}" {if $WIDGETINFO['data']['relatedmodule'] == $item['related_tabid']}selected{/if} >{\App\Language::translate($item['label'], $item['name'])}</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('LBL_SELECTING_FIELDS', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('LBL_SELECTING_FIELDS_INFO', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('LBL_SELECTING_FIELDS', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls">
									<select name="relatedfields" multiple class="chzn-select form-control col-md-12" data-validation-engine="validate[required]">
										{foreach from=$RELATEDMODULES item=RELATED_MODULE key=key}
											{foreach from=Vtiger_Module_Model::getInstance($RELATED_MODULE['name'])->getFieldsByBlocks() key=BLOCK_NAME item=FIELDS}
												<optgroup label="{\App\Language::translate($BLOCK_NAME, $RELATED_MODULE['name'])}" data-module="{$RELATED_MODULE['related_tabid']}">
													{foreach from=$FIELDS item=FIELD_MODEL key=FIELD_NAME}
														<option value="{$RELATED_MODULE['related_tabid']}::{$FIELD_NAME}" {if $WIDGETINFO['data']['relatedfields'] && in_array($RELATED_MODULE['related_tabid']|cat:'::'|cat:$FIELD_NAME, $WIDGETINFO['data']['relatedfields'])}selected{/if} data-module="{$RELATED_MODULE['related_tabid']}">{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATED_MODULE['name'])}</option>
													{/foreach}
												</optgroup>
											{/foreach}
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('LBL_VIEW_TYPE', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('LBL_VIEW_TYPE_INFO', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('LBL_VIEW_TYPE', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls">
									<select name="viewtype" class="select2">
										<option value="List" {if $WIDGETINFO['data']['viewtype'] == 'List'}selected{/if}>{\App\Language::translate('LBL_LIST', $QUALIFIED_MODULE)}</option>
										<option value="Summary" {if $WIDGETINFO['data']['viewtype'] == 'Summary'}selected{/if}>{\App\Language::translate('LBL_SUMMARY', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>		
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Limit entries info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls">
									<input name="limit" class="form-control" type="text" value="{$WIDGETINFO['data']['limit']}" />
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini">
								<label class="col-md-4 control-label">{\App\Language::translate('No left margin', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('No left margin', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls">
									<input name="nomargin" class="switchBtn switchBtnReload" type="checkbox" {if $WIDGETINFO['nomargin'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini">
								<label class="col-md-4 control-label">{\App\Language::translate('Add button', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Add button info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Add button', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7">
									<input name="action" class="switchBtn switchBtnReload" type="checkbox" {if isset($WIDGETINFO['data']['action']) && $WIDGETINFO['data']['action'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini">
								<label class="col-md-4 control-label">{\App\Language::translate('Select button', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('LBL_SELECT_BUTTON_INFO', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Select button', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls form-switch-mini">
									<input name="actionSelect" class="switchBtn switchBtnReload" type="checkbox" {if isset($WIDGETINFO['data']['actionSelect']) && $WIDGETINFO['data']['actionSelect'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini">
								<label class="col-md-4 control-label">{\App\Language::translate('No message', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('No message info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('No message', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls">
									<input name="no_result_text" class="switchBtn switchBtnReload" type="checkbox" {if isset($WIDGETINFO['data']['no_result_text']) && $WIDGETINFO['data']['no_result_text'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
								</div>
							</div>
							{*<div class="form-group form-group-sm form-switch-mini">
							<label class="col-md-4 control-label">{\App\Language::translate('LBL_SHOW_ALL_RECORDS', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('LBL_SHOW_ALL_RECORDS_INFO', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('LBL_SHOW_ALL_RECORDS', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
							<div class="col-md-7 controls">
							<input name="showAll" class="switchBtn switchBtnReload" type="checkbox" {if $WIDGETINFO['data']['showAll'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
							</div>
							</div>*}
							<div class="form-group form-group-sm hide">
								<label class="col-md-4 control-label">{\App\Language::translate('LBL_SHITCH_HEADER', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('LBL_SHITCH_HEADER_INFO', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('LBL_SHITCH_HEADER', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7">
									<input type="hidden" id="switchHeader_selected" value="{$WIDGETINFO['data']['switchHeader']}">
									<select name="switchHeader" class="select2 form-control marginLeftZero">
										<option value="-">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('Filter', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Filter info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Filter', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls">
									<input type="hidden" id="filter_selected" value="{$WIDGETINFO['data']['filter']}">
									<select name="filter" class="select2 form-control marginLeftZero">
										<option value="-">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('Switch', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Switch info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Switch', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls">
									<input type="hidden" id="checkbox_selected" value="{$WIDGETINFO['data']['checkbox']}">
									<select name="checkbox" class="select2 form-control marginLeftZero">
										<option value="-">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('ModalFooter.tpl', $QUALIFIED_MODULE)}
				</form>
			</div>
		</div>
	</div>
{/strip}
