{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="summaryWidgetContainer BasicComments updatesWidgetContainer">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}">
			<div class="widget_header">
				<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}" />
				<div class="row">
					<div class="col-xs-9 col-md-5 col-sm-6">
						<div class="widgetTitle textOverflowEllipsis">
							<h4 class="modCT_{$WIDGET['label']}">
								{if $WIDGET['label'] eq ''}
									{\App\Language::translate($RELATED_MODULE_NAME,$RELATED_MODULE_NAME)}
								{else}
									{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
								{/if}
							</h4>
						</div>
					</div>
					{if $WIDGET['level'] < 2}
						<div class="col-8 col-md-4 col-sm-3 paddingBottom10">
							<input class="switchBtn switchBtnReload filterField" type="checkbox" checked="" data-size="small" data-label-width="5" data-on-text="{$WIDGET['switchHeaderLables']['on']}" data-off-text="{$WIDGET['switchHeaderLables']['off']}" data-urlparams="search_params" data-on-val='{\App\Purifier::encodeHtml($WIDGET['switchHeader']['on'])}' data-off-val='{\App\Purifier::encodeHtml($WIDGET['switchHeader']['off'])}'>
						</div>
					{/if}
				</div>
				<hr class="widgetHr" />
			</div>
			<div class="widget_contents">
			</div>
		</div>
	</div>
{/strip}
