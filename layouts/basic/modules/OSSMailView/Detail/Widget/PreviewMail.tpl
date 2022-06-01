{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-OSSMailView-Detail-Widget-PreviewMail -->
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId($WIDGET['id']|cat:_)}"}
	<div class="c-detail-widget js-detail-widget" data-name="{$WIDGET['label']}" data-module-name=""
		data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}"
			data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}">
			<div class="c-detail-widget__header js-detail-widget-header collapsed border-bottom-0"
				data-js="container|value">
				<input type="hidden" name="relatedModule" value="" />
				<div class="c-detail-widget__header__container d-flex align-items-center py-1">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
						data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					</div>
					<div class="c-detail-widget__header__title">
						<h5 class="mb-0 text-truncate">
							{if empty($WIDGET['label'])}
								{App\Language::translate('emailPreviewHeader',$MODULE_NAME)}
							{else}
								{App\Language::translate($WIDGET['label'],$MODULE_NAME)}
							{/if}
						</h5>
					</div>
					<div class="row inline justify-center js-hb__container ml-auto">
						<button type="button" tabindex="0" class="btn js-hb__btn u-hidden-block-btn text-grey-6 py-0 px-1">
							<div class="text-center col items-center justify-center row">
								<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
							</div>
						</button>
					</div>
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-collapse collapse multi-collapse pt-0"
				id="{$WIDGET_UID}-collapse" data-storage-key="{$WIDGET_UID}" aria-labelledby="{$WIDGET_UID}">
				<div class="js-detail-widget-content" data-js="container|value"></div>
			</div>
		</div>
	</div>
	<!-- /tpl-OSSMailView-Detail-Widget-PreviewMail -->
{/strip}
