{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="summaryWidgetContainer BasicComments updatesWidgetContainer">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}">
			<div class="widget_header">
				<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}" />
				<div class="row mb-1">
					<div class="col-xs-9 col-md-5 col-sm-6">
						<div class="widgetTitle u-text-ellipsis">
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
						<div class="btn-group btn-group-toggle hierarchyButtons" data-toggle="buttons">
							<label class="btn btn-secondary active">
								<input class="hierarchyComments" type="radio" name="options" id="option1" value="current" autocomplete="off" checked> {\App\Language::translate('LBL_COMMENTS_0', 'ModComments')}
							</label>
							<label class="btn btn-secondary">
								<input class="hierarchyComments" type="radio" name="options" id="option2" value="all" autocomplete="off"> {\App\Language::translate('LBL_ALL_RECORDS', 'ModComments')}
							</label>
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
