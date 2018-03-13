{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="summaryWidgetContainer">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="widget_header">
				<div class="row">
					<div class="col-9 col-md-5 col-sm-6">
						<div class="widgetTitle u-text-ellipsis">
							<h4 class="modCT_{$WIDGET['label']}">
								{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
							</h4>
						</div>
					</div>
					{if isset($WIDGET['switchHeader'])}
						<div class="col-8 col-md-4 col-sm-3 paddingBottom10">
							<input class="switchBtn switchBtnReload filterField" type="checkbox" checked="" data-size="small" data-label-width="5" data-on-text="{$WIDGET['switchHeaderLables']['on']}" data-off-text="{$WIDGET['switchHeaderLables']['off']}" data-urlparams="whereCondition" data-on-val='{$WIDGET['switchHeader']['on']}' data-off-val='{$WIDGET['switchHeader']['off']}'>
						</div>
					{/if}
					<div class="col-md-3 col-sm-3 float-right paddingBottom10">
						<div class="float-right">
							<div class="btn-group">
								{if $WIDGET['newChanege'] && $MODULE_MODEL->isPermitted('ReviewingUpdates') && $USER_MODEL->getId() eq $USER_MODEL->getRealId()}
									<div class="float-right btn-group">
										<button id="btnChangesReviewedOn" type="button" class="btn btn-success btn-sm btnChangesReviewedOn" title="{\App\Language::translate('BTN_CHANGES_REVIEWED_ON', $WIDGET['moduleBaseName'])}">
											<span class="far fa-check-circle"></span>
										</button>
									</div>
								{/if}
							</div>
						</div>
					</div>
				</div>
				<hr class="widgetHr" />
			</div>
			<div class="widget_contents">
			</div>
		</div>
	</div>
{/strip}
