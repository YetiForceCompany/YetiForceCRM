{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-ProductsServicesBasic -->
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}"}
	<div class="c-detail-widget js-detail-widget productsServicesWidgetContainer" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}"
			 data-name="{$WIDGET['label']}">
			<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
				<div class="d-flex align-items-center py-1 w-100">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse" data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
						<span class="mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"></span>
					</div>
					<h5 class="mb-0 mr-1">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
					<div class="ml-auto btn-group flex-wrap">
						{assign var="DEFAULT_MODULE" value='Products'}
						{if isset($WIDGET['data']['filter'])}
							{assign var="DEFAULT_MODULE" value=$WIDGET['data']['filter']}
						{/if}
						{foreach name=BTN item=COUNT key=MODULE_DATA from=Products_SummaryWidget_Model::getModulesAndCount($RECORD)}
							<label class="btn btn-sm btn-light mb-0 js-switch__btn u-cursor-pointer {if $DEFAULT_MODULE eq $MODULE_DATA}active{/if}"
										title="{App\Language::translate($MODULE_DATA,$MODULE_DATA)}" data-js="class: active">
								<input type="radio" name="mod" class="js-switch" value="{$MODULE_DATA}"
											data-off-val="{$MODULE_DATA}" data-urlparams="mod" data-js="change"
											{if $DEFAULT_MODULE eq $MODULE_DATA} checked="checked"{/if}>
								<span class="mx-1 userIcon-{$MODULE_DATA}"></span>
								<span class="badge">{$COUNT}</span>
							</label>
						{/foreach}
					</div>
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse" data-storage-key="{$WIDGET['id']}"  aria-labelledby="{$WIDGET_UID}" data-js="container|value">
			</div>
		</div>
	</div>
<!-- /tpl-Base-Detail-Widget-ProductsServicesBasic -->
{/strip}
