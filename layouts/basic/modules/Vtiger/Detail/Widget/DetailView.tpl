{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-DetailView -->
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId($WIDGET['id']|cat:_)}"}
	<div class="tpl-Detail-Widget-DetailView c-detail-widget js-detail-widget" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['type']}">
			<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
				<div class="c-detail-widget__header__container d-flex w-100 align-items-center py-1">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
						data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					</div>
					<h5 class="mb-0" title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}">
						{if $WIDGET['label'] eq ''}
							{App\Language::translate('LBL_RECORD_DETAILS',$MODULE_NAME)}
						{else}
							{App\Language::translate($WIDGET['label'],$MODULE_NAME)}
						{/if}
					</h5>
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse"
				data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-DetailView -->
{/strip}
