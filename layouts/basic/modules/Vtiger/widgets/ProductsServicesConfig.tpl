{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget">  
					<input type="hidden" name="wid" value="{$WID}" />
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<button type="button" data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">×</button>
						<h3 id="massEditHeader" class="modal-title">{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}</h3>
					</div>
					<div class="modal-body">
						<div class="modal-Fields">
							<div class="form-horizontal">
								<div class="form-group">
									<div class="col-md-3"><strong>{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}<strong>:</div>
												<div class="col-md-7">
													{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
												</div>
												</div>
												<div class="form-group">
													<div class="col-md-3"><label class="col-form-label">{\App\Language::translate('Label', $QUALIFIED_MODULE)}:</label></div>
													<div class="col-md-7"><input name="label" class="form-control" type="text" value="{$WIDGETINFO['label']}" /></div>
												</div>
												<div class="form-group">
													<div class="col-md-3"><label>{\App\Language::translate('No left margin', $QUALIFIED_MODULE)}:</label></div>
													<div class="col-md-7">
														<input name="nomargin" class="" type="checkbox" value="1" {if $WIDGETINFO['nomargin'] == 1}checked{/if}/>
														<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('No left margin', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>
													</div>
												</div>
												<div class="form-group">
													<div class="col-md-3"><label class="col-form-label">{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}:</label></div>
													<div class="col-md-7">
														<div class="col-3 paddingLRZero">
															<input name="limit" class="form-control" type="text" value="{$WIDGETINFO['data']['limit']}" />
														</div>
														<a href="#" class="HelpInfoPopover" title="" data-placement="top" data-content="{\App\Language::translate('Limit entries info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>
													</div>
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
