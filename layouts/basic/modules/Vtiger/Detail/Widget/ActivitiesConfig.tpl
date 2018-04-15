{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget">  
					<input type="hidden" name="wid" value="{$WID}" />
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<h3 id="massEditHeader" class="modal-title">{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}</h3>
						<button type="button" data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">&times;</button>
					</div>
					<div class="modal-body">
						<div class="modal-Fields">
							<div class="row">
								<div class="col-md-3 marginLeftZero"><strong>{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}</strong>:</div>
								<div class="col-md-7">
									{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
								</div>
								<div class="col-md-3 marginLeftZero"><label class="">{\App\Language::translate('Label', $QUALIFIED_MODULE)}:</label></div>
								<div class="col-md-7"><input name="label" class="form-control" type="text" value="{$WIDGETINFO['label']}" /></div>
								<div class="col-md-3 marginLeftZero">
									<label class="">
										{\App\Language::translate('No left margin', $QUALIFIED_MODULE)}:
										<a href="#" class="js-help-info" title="" data-placement="top" data-content="{\App\Language::translate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('No left margin', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>
									</label>
								</div>
								<div class="col-md-7">
									<input name="nomargin" class="" type="checkbox" value="1" {if $WIDGETINFO['nomargin'] == 1}checked{/if}/>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-3 marginLeftZero">
									<label>
										{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}:
										<a href="#" class="js-help-info" title="" data-placement="top" data-content="{\App\Language::translate('Limit entries info', $QUALIFIED_MODULE)}" data-original-title="{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}"><i class="fas fa-info-circle"></i></a>
									</label>
								</div>
								<div class="col-md-7">
									<input name="limit" class="form-control" type="text" value="{$WIDGETINFO['data']['limit']}" />
								</div>
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE)}
				</form>
			</div>
		</div>
	</div>
{/strip}
