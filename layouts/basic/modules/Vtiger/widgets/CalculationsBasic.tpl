<div class="summaryWidgetContainer calculationsWidgetContainer">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		<div class="widget_header row">
			<span class="col-md-12 margin0px">
				<div class="row">
					<div class="col-md-4">
						<span class="{$span} margin0px"><h4 class="moduleColor_{$WIDGET['relatedmodule']}">{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
					</div>
					<div class="col-md-4" align="right">
						<input class="switchBtn calculationsSwitch" type="checkbox" checked="" data-size="small" data-label-width="5" data-handle-width="100" data-on-text="{vtranslate('LBL_OPEN',$WIDGET['relatedmodule'])}" data-off-text="{vtranslate('LBL_ARCHIVE',$WIDGET['relatedmodule'])}">
					</div>
					<div class="col-md-4" align="right">
						{assign var="RELFIELD" value=''}
						{if $RECORD->getModuleName() == 'Potentials'}
							{assign var="RELFIELD" value='&potentialid='|cat:$RECORD->getId()}
						{/if}
						<a class="btn btn-default" href="index.php?module={$WIDGET['relatedmodule']}&view=Edit&sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true{$RELFIELD}">
							<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
						</a>
					</div>
				</div>
			</span>
		</div>
		<div class="widget_contents">
		</div>
	</div>
</div>
