<div class="summaryWidgetContainer potentialsWidgetContainer">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		<div class="widget_header row">
			<span class="col-md-12 margin0px">
				<div class="row">
					<div class="col-md-4">
						<span class="{$span} margin0px"><h4 class="moduleColor_{$WIDGET['relatedmodule']}">{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
					</div>
					<div class="col-md-4" align="right">
						<input class="switchBtn potentialsSwitch" type="checkbox" checked="" data-size="small" data-label-width="5" data-handle-width="100" data-on-text="{vtranslate('LBL_OPEN',$WIDGET['relatedmodule'])}" data-off-text="{vtranslate('LBL_ARCHIVE',$WIDGET['relatedmodule'])}">
					</div>
					{assign var=ACCESSIBLE_GROUP_LIST value=$USER_MODEL->getAccessibleGroupForModule($MODULE_NAME)}
					{assign var=POTENTIALS value=Settings_SalesProcesses_Module_Model::getConfig('potential')}
					{if $MODULE_NAME eq 'Accounts' && $POTENTIALS.add_potential eq 'true' && array_key_exists($RECORD->get('assigned_user_id'),$ACCESSIBLE_GROUP_LIST)}
						{assign var=ADD_BUTTON value=1}
					{/if}
					<div class="col-md-4 {if $ADD_BUTTON } hide {/if}" align="right">
						<a class="btn createRecordFromFilter" data-url="index.php?module={$WIDGET['relatedmodule']}&view=QuickCreateAjax" data-prf="related_to">
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
