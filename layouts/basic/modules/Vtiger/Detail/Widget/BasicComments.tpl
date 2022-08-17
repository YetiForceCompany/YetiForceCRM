{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId($WIDGET['id']|cat:_)}"}
	{assign var=RELATED_MODULE_NAME value=$WIDGET['data']['relatedmodule']}
	<div class="tpl-Detail-Widget-BasicComments c-detail-widget js-detail-widget BasicComments updatesWidgetContainer"
		data-js=”container”>
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}"
			data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}"
			data-limit="{$WIDGET['limit']}" data-js="data-url|data-type|data-limit">
			<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
				<input type="hidden" name="relatedModule" value="{$RELATED_MODULE_NAME}" />
				<div class="c-detail-widget__header__container d-flex align-items-center my-1">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
						data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					</div>
					<div class="c-detail-widget__header__title">
						<h5 class="mb-0 modCT_{$RELATED_MODULE_NAME}">
							{if $WIDGET['label'] eq ''}
								{\App\Language::translate($RELATED_MODULE_NAME,$RELATED_MODULE_NAME)}
							{else}
								{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
							{/if}
						</h5>
					</div>
					<div class="row inline justify-center js-hb__container ml-auto">
						<button type="button" tabindex="0" class="btn js-hb__btn u-hidden-block-btn text-grey-6 py-0 px-1">
							<div class="text-center col items-center justify-center row">
								<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
							</div>
						</button>
						<div class="u-hidden-block items-center js-comment-actions d-lg-flex">
							{if $HIERARCHY !== false && $HIERARCHY < 2}
								<div class="mr-1">
									<div class="btn-group btn-group-toggle" data-toggle="buttons">
										<label class="js-hierarchy-comments-btn u-text-ellipsis btn-sm mt-1 mt-sm-0 btn btn-outline-primary {if in_array('current', $HIERARCHY_VALUE)}active{/if}"
											title="{\App\Language::translate('LBL_COMMENTS_0', 'ModComments')}" data-js="click">
											<input name="options" type="checkbox"
												class="js-hierarchy-comments"
												data-js="val"
												value="current"
												{if in_array('current', $HIERARCHY_VALUE)} checked="checked" {/if}
												autocomplete="off" />
											{\App\Language::translate('LBL_COMMENTS_0', 'ModComments')}
										</label>
										<label class="js-hierarchy-comments-btn u-text-ellipsis btn-sm mt-1 mt-sm-0 btn btn-outline-primary {if in_array('related', $HIERARCHY_VALUE)}active{/if}"
											title="{\App\Language::translate('LBL_ALL_RECORDS', 'ModComments')}" data-js="click">
											<input name="options" type="checkbox"
												class="js-hierarchy-comments"
												data-js="val"
												value="related"
												{if in_array('related', $HIERARCHY_VALUE)} checked="checked" {/if}
												autocomplete="off" />
											{\App\Language::translate('LBL_ALL_RECORDS', 'ModComments')}
										</label>
									</div>
								</div>
							{/if}
							<div class="input-group input-group-sm">
								<input type="text" class="js-comment-search form-control"
									placeholder="{\App\Language::translate('LBL_COMMENTS_SEARCH','ModComments')}"
									aria-describedby="commentSearchAddon" data-container="widget" data-js="keypress|data">
								<div class="input-group-append">
									<button class="btn btn-light js-search-icon" type="button" data-js="click">
										<span class="fas fa-search fa-fw" title="{\App\Language::translate('LBL_SEARCH')}"></span>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse"
				data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
			</div>
		</div>
	</div>
{/strip}
