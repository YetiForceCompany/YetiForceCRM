{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="summaryWidgetContainer">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{Vtiger_Util_Helper::toSafeHTML($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="widget_header">
				<div class="row">
					<div class="col-xs-9 col-md-5 col-sm-6">
						<div class="widgetTitle textOverflowEllipsis">
							<h4 class="moduleColor_{$WIDGET['label']}">
								{vtranslate($WIDGET['label'],$MODULE_NAME)}
							</h4>
						</div>
					</div>
					{if isset($WIDGET['switchHeader'])}
						<div class="col-xs-8 col-md-4 col-sm-3 paddingBottom10">
							<input class="switchBtn switchBtnReload filterField" type="checkbox" checked="" data-size="small" data-label-width="5" data-on-text="{$WIDGET['switchHeaderLables']['on']}" data-off-text="{$WIDGET['switchHeaderLables']['off']}" data-urlparams="whereCondition" data-on-val='{$WIDGET['switchHeader']['on']}' data-off-val='{$WIDGET['switchHeader']['off']}'>
						</div>
					{/if}
					<div class="col-md-3 col-sm-3 pull-right paddingBottom10">
						<div class="pull-right">
							<div class="btn-group">
								{if $WIDGET['newChanege'] && $MODULE_MODEL->isPermitted('ReviewingUpdates') && $USER_MODEL->getId() eq $USER_MODEL->getRealId()}
									<div class="pull-right btn-group">
										<button id="btnChangesReviewedOn" type="button" class="btn btn-success btn-sm btnChangesReviewedOn" title="{vtranslate('BTN_CHANGES_REVIEWED_ON', $WIDGET['moduleBaseName'])}">
											<span class="glyphicon glyphicon-ok-circle"></span>
										</button>
									</div>
								{/if}
							</div>
						</div>
					</div>
				</div>
				<hr class="widgetHr"/>
			</div>
			<div class="widget_contents">
			</div>
		</div>
	</div>
{/strip}
