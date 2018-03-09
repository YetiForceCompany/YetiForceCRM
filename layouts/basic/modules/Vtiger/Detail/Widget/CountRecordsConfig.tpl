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
								<label class="col-md-4 col-form-label">{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}:</label>
								<div class="col-md-7 form-control-plaintext">
									{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
								</div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Label', $QUALIFIED_MODULE)}:</label>
								<div class="col-md-7 controls"><input name="label" class="form-control" type="text" value="{$WIDGETINFO['label']}" /></div>
							</div>
							<div class="form-group form-group-sm">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Related module', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Related module info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Related module', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls">
									<select multiple name="relatedModules" class="select2 form-control marginLeftZero" data-validation-engine="validate[required]">
										{foreach from=$RELATEDMODULES item=item key=key}
											<option value="{$item['related_tabid']}" {if in_array($item['related_tabid'], $WIDGETINFO['data']['relatedModules']) }selected{/if} >{\App\Language::translate($item['label'], $item['name'])}</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini">
								<label class="col-md-4 col-form-label">{\App\Language::translate('No left margin', $QUALIFIED_MODULE)}<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('No left margin', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 controls">
									<input name="nomargin" class="switchBtn switchBtnReload" type="checkbox" {if $WIDGETINFO['nomargin'] == 1}checked{/if} data-size="mini" data-label-width="5" data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}" value="1">
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
