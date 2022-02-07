{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{foreach from=$DATA item=REL_MODULE}
		<div class="c-detail-widget js-detail-widget noSumarryWidgetEffect" data-js="container">
			<div class="card">
				<div class="card-header paddingTBZero">
					<h5 class="mb-1">{\App\Language::translate($REL_MODULE, $REL_MODULE)}</h5>
				</div>
				<div class="widgetContainer_{$REL_MODULE} widgetContentBlock"
					data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule={$REL_MODULE}&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					data-name="{$REL_MODULE}">
					<div class="js-detail-widget-content" data-js="container|value">

					</div>
				</div>
			</div>
		</div>
	{/foreach}
{/strip}
