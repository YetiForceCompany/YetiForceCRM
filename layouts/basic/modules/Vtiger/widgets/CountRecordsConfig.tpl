{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget form-horizontal validateForm">
					<input type="hidden" name="wid" value="{$WID}">
					<input type="hidden" name="type" value="{$TYPE}">
					<div class="modal-header">
						<button type="button" data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE', $QUALIFIED_MODULE)}">×</button>
						<h3 id="massEditHeader" class="modal-title">{vtranslate('Add widget', $QUALIFIED_MODULE)}</h3>
					</div>
					<div class="modal-body">
						<div class="form-container-sm">
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{vtranslate('Type widget', $QUALIFIED_MODULE)}:</label>
								<div class="col-md-7 form-control-static">
									{vtranslate($TYPE, $QUALIFIED_MODULE)}
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{vtranslate('Label', $QUALIFIED_MODULE)}:</label>
								<div class="col-md-7 controls"><input name="label" class="form-control" type="text" value="{$WIDGETINFO['label']}" /></div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 control-label">{vtranslate('Related module', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{vtranslate('Related module info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Related module', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7 controls">
									<select multiple name="relatedModules" class="select2 form-control marginLeftZero" data-validation-engine="validate[required]">
										{foreach from=$RELATEDMODULES item=item key=key}
											<option value="{$item['related_tabid']}" {if in_array($item['related_tabid'], $WIDGETINFO['data']['relatedModules']) }selected{/if} >{vtranslate($item['label'], $item['name'])}</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini">
								<label class="col-md-4 control-label">{vtranslate('No left margin', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{vtranslate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('No left margin', $QUALIFIED_MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>:</label>
								<div class="col-md-7 controls">
									<input name="nomargin" class="switchBtn switchBtnReload" type="checkbox" {if $WIDGETINFO['nomargin'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{vtranslate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
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
