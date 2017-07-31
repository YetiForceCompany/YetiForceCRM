{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget form-horizontal validateForm">
					<input type="hidden" name="wid" value="{$WID}">
					<input type="hidden" name="type" value="{$TYPE}">
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
								<label class="col-md-4 control-label">{\App\Language::translate('Related module', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Related module info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Related module', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7 controls">
									<select name="relatedmodule" class="select2 form-control marginLeftZero" data-validation-engine="validate[required]">
										{foreach from=$RELATEDMODULES item=item key=key}
											<option value="{$item['related_tabid']}" {if $WIDGETINFO['data']['relatedmodule'] == $item['related_tabid']}selected{/if} >{\App\Language::translate($item['label'], $item['name'])}</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Limit entries info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7 controls">
									<input name="limit" class="form-control" type="text" value="{$WIDGETINFO['data']['limit']}"/>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('Columns', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Columns info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Columns', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7 controls">
									<select name="columns" class="select2 form-control marginLeftZero">
										{foreach from=$MODULE_MODEL->getColumns() item=item key=key}
											<option value="{$item}" {if $WIDGETINFO['data']['columns'] == $item}selected{/if} >{$item}</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini">
								<label class="col-md-4 control-label">{\App\Language::translate('No left margin', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('No left margin', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7 controls">
									<input name="nomargin" class="switchBtn switchBtnReload" type="checkbox" {if $WIDGETINFO['nomargin'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini">
								<label class="col-md-4 control-label">{\App\Language::translate('Add button', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Add button info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Add button', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7">
									<input name="action" class="switchBtn switchBtnReload" type="checkbox" {if isset($WIDGETINFO['data']['action']) && $WIDGETINFO['data']['action'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini">
								<label class="col-md-4 control-label">{\App\Language::translate('Select button', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('LBL_SELECT_BUTTON_INFO', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Select button', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7 controls form-switch-mini">
									<input name="actionSelect" class="switchBtn switchBtnReload" type="checkbox" {if isset($WIDGETINFO['data']['actionSelect']) && $WIDGETINFO['data']['actionSelect'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini">
								<label class="col-md-4 control-label">{\App\Language::translate('No message', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('No message info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('No message', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7 controls">
									<input name="no_result_text" class="switchBtn switchBtnReload" type="checkbox" {if isset($WIDGETINFO['data']['no_result_text']) && $WIDGETINFO['data']['no_result_text'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
								</div>
							</div>
							{*<div class="form-group form-group-sm form-switch-mini">
							<label class="col-md-4 control-label">{\App\Language::translate('LBL_SHOW_ALL_RECORDS', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('LBL_SHOW_ALL_RECORDS_INFO', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('LBL_SHOW_ALL_RECORDS', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
							<div class="col-md-7 controls">
							<input name="showAll" class="switchBtn switchBtnReload" type="checkbox" {if $WIDGETINFO['data']['showAll'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
							</div>
							</div>*}
							<div class="form-group form-group-sm hide">
								<label class="col-md-4 control-label">{\App\Language::translate('LBL_SHITCH_HEADER', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('LBL_SHITCH_HEADER_INFO', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('LBL_SHITCH_HEADER', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7">
									<input type="hidden" id="switchHeader_selected" value="{$WIDGETINFO['data']['switchHeader']}">
									<select name="switchHeader" class="select2 form-control marginLeftZero">
										<option value="-">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('Filter', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Filter info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Filter', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7 controls">
									<input type="hidden" id="filter_selected" value="{$WIDGETINFO['data']['filter']}">
									<select name="filter" class="select2 form-control marginLeftZero">
										<option value="-">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{\App\Language::translate('Switch', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Switch info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Switch', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7 controls">
									<input type="hidden" id="checkbox_selected" value="{$WIDGETINFO['data']['checkbox']}">
									<select name="checkbox" class="select2 form-control marginLeftZero">
										<option value="-">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
				</form>
			</div>
		</div>
	</div>
{/strip}
