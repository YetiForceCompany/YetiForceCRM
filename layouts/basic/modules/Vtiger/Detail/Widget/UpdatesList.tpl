{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-UpdatesList -->
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}"}
	<div class="tpl-Detail-Widget-Basic c-detail-widget js-detail-widget" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}">
			<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
				<div class="c-detail-widget__header__container d-flex align-items-center py-1">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse" data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					</div>
					<div class="c-detail-widget__header__title">
						<h5 class="mb-0 text-truncate">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
					</div>
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-collapse collapse multi-collapse pt-0" id="{$WIDGET_UID}-collapse" data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}">
				{if !empty($WIDGET['data']['field_name']) && is_array($WIDGET['data']['field_name']) && count($WIDGET['data']['field_name']) > 1}
					<div class="form-group-sm w-100 mr-2 mt-2">
						<select name="field_name" class="select2 form-control form-control-sm js-filter_field" data-urlparams="field" data-return="value" data-js="change">
							{foreach item=VALUE key=KEY from=$WIDGET['data']['field_name']}
								{assign var=FIELD_MODEL value=$MODULE_MODEL->getFieldByName($VALUE)}
								{if $FIELD_MODEL}
									<option value="{$VALUE}">{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}</option>
								{/if}
							{/foreach}
						</select>
					</div>
				{/if}
				<div class="js-detail-widget-content" data-js="container|value"></div>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-UpdatesList -->
{/strip}
