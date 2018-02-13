{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{foreach from=$DATA item=REL_MODULE}
		{$ITEM}
		<div class="summaryWidgetContainer noSumarryWidgetEffect">
			<div class="widgetContainer_assets2 panel panel-default" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule={$REL_MODULE}&mode=showRelatedRecords&page=1&limit={$LIMIT}">
				<div class="panel-heading paddingTBZero widget_header">
					<input type="hidden" name="relatedModule" value="{$REL_MODULE}" />
					<div class="panel-title row">
						<h4 class="col-8">{\App\Language::translate($REL_MODULE, $REL_MODULE)}</h4>
					</div>
				</div>
				<div class="widget_contents panel-body padding0">
				</div>
			</div>
		</div>
	{/foreach}
{/strip}
