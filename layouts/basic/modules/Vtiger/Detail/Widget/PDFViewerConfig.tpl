{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-PDFViewerConfig -->
	<div class="tpl-Detail-Widget-SummaryCategoryConfig modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget">
					{if !empty($WID)}<input type="hidden" name="wid" value="{$WID}" />{/if}
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<h5 id="massEditHeader" class="modal-title">{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}</h5>
						<button type="button" data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">&times;
						</button>
					</div>
					<div class="modal-body">
						{if !\App\YetiForce\Shop::check('YetiForceWidgets')}
							<div class="alert alert-warning">
								<span class="yfi-premium mr-2 u-fs-3x color-red-600 float-left"></span>
								{\App\Language::translate('LBL_PAID_FUNCTIONALITY', 'Settings::YetiForce')} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceMagento&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
							</div>
						{/if}
						<div class="modal-Fields">
							<div class="row">
								<div class="col-md-4">
									<strong>{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}</strong>:
								</div>
								<div class="col-md-7">
									{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
								</div>
								<div class="col-md-4">
									<label class="col-form-label">{\App\Language::translate('Label', $QUALIFIED_MODULE)}:</label>
								</div>
								<div class="col-md-7">
									<input name="label" class="form-control" type="text" data-validation-engine="validate[required]" value="{$WIDGETINFO['label']}" />
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_DEFAULT_ACTIVE', $QUALIFIED_MODULE)}:</label>
								<div class="col-md-7">
									{assign var=DEFAULT_ACTIVE value=isset($WIDGETINFO['data']['action']) && $WIDGETINFO['data']['action'] == 1}
									<div class="btn-group btn-group-toggle" data-toggle="buttons">
										<label class="btn btn-sm btn-outline-primary {if $DEFAULT_ACTIVE} active{/if}">
											<input type="radio" name="action"
												id="option1" autocomplete="off" value="1"
												{if $DEFAULT_ACTIVE} checked="true" {/if}> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
										</label>
										<label class="btn btn-sm btn-outline-primary {if !$DEFAULT_ACTIVE} active{/if}">
											<input type="radio" name="action"
												id="option2" autocomplete="off" value="0"
												{if !$DEFAULT_ACTIVE} checked="true" {/if}> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
										</label>
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
	<!-- /tpl-Base-Detail-Widget-PDFViewerConfig -->
{/strip}
