{strip}
<div class="summaryWidgetContainer productsServicesWidgetContainer">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		<div class="widget_header row">
			<span class="col-md-12 margin0px">
				<div class="row">
					<div class="col-md-4">
						<span class="{$span} margin0px"><h4>{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
					</div>
					<div class="col-md-4" align="right">
						<input class="switchBtn switchBtnReload" type="checkbox" checked="" data-size="small" data-label-width="5" data-handle-width="100" data-on-text="{vtranslate('Products','Products')}" data-off-text="{vtranslate('Services','Services')}" data-urlparams="mod" data-on-val="Products" data-off-val="Services">
					</div>
				</div>
			</span>
		</div>
		<div class="widget_contents">
		</div>
	</div>
</div>
{/strip}
