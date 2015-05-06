<div class="summaryWidgetContainer calculationsWidgetContainer">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		<div class="widget_header row-fluid">
			<span class="span8 margin0px">
				<div class="row-fluid">
					<div class="span4">
						<span class="{$span} margin0px"><h4 class="moduleColor_{$WIDGET['relatedmodule']}">{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
					</div>
					<div class="span8" align="right">
						<input class="switchBtn calculationsSwitch" type="checkbox" checked="" data-size="small" data-label-width="5" data-handle-width="100" data-on-text="{vtranslate('LBL_OPEN',$WIDGET['relatedmodule'])}" data-off-text="{vtranslate('LBL_ARCHIVE',$WIDGET['relatedmodule'])}">
					</div>
				</div>
			</span>
		</div>
		<div class="widget_contents">
		</div>
	</div>
</div>
