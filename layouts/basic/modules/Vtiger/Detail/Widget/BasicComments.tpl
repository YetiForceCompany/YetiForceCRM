{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="c-detail-widget u-mb-13px js-detail-widget BasicComments updatesWidgetContainer" data-js=”container”>
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}">
			<div class="c-detail-widget__header js-detail-widget-header" data-js=”container|value>
				<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}" />
				<div class="form-row align-items-center my-1">
					<div class="col-9 col-md-5 col-sm-6">
						<div class="widgetTitle u-text-ellipsis">
							<h5 class="mb-0 modCT_{$WIDGET['label']}">
								{if $WIDGET['label'] eq ''}
									{\App\Language::translate($RELATED_MODULE_NAME,$RELATED_MODULE_NAME)}
								{else}
									{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
								{/if}
							</h5>
						</div>
					</div>
					{if $WIDGET['level'] < 2}
						<div class="btn-group btn-group-toggle hierarchyButtons" data-toggle="buttons">
							<label class="btn btn-sm btn-outline-primary active">
								<input class="hierarchyComments" type="radio" name="options" id="option1" value="current" autocomplete="off" checked> {\App\Language::translate('LBL_COMMENTS_0', 'ModComments')}
							</label>
							<label class="btn btn-sm btn-outline-primary">
								<input class="hierarchyComments" type="radio" name="options" id="option2" value="all" autocomplete="off"> {\App\Language::translate('LBL_ALL_RECORDS', 'ModComments')}
							</label>
						</div>
					{/if}
				</div>
				<hr class="widgetHr" />
			</div>
			<div class="c-detail-widget__content js-detail-widget-content" data-js=”container|value”>
			</div>
		</div>
	</div>
{/strip}
