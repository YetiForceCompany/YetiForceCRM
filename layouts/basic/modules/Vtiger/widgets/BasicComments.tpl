{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
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
					{if count($HIERARCHY_LIST) != 1}
						<div class="col-md-7 commentsHeader">
							<select class="select2 form-control hierarchyComments" multiple="multiple">
								{foreach key=NAME item=LABEL from=$WIDGET['hierarchyList']}
									<option value="{$NAME}" {if in_array($NAME, $WIDGET['hierarchy'])}selected{/if}>{\App\Language::translate($LABEL, 'ModComments')}</option>
								{/foreach}
							</select>
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
