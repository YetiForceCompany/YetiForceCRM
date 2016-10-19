{strip}
	<div class="summaryWidgetContainer">
		<div class="widget_header row">
			<span class="col-md-5 margin0px"><h4>{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
		</div>
		<div class="defaultMarginP">
			{assign var=FULL_TEXT value=vtlib\Functions::removeHtmlTags(array('link', 'style', 'img', 'script', 'base'),decode_html($RECORD->get($WIDGET['data']['field_name'])))}
			<div class="moreContent table-responsive">
				<span class="teaserContent">
					{Vtiger_Util_Helper::toVtiger6SafeHTML($FULL_TEXT)|substr:0:600}
				</span>
				{if $FULL_TEXT|strlen > 600}
					<span class="fullContent hide">
						{$FULL_TEXT}
					</span>
					<button type="button" class="btn btn-info btn-xs moreBtn" data-on="{vtranslate('LBL_MORE_BTN')}" data-off="{vtranslate('LBL_HIDE_BTN')}">{vtranslate('LBL_MORE_BTN')}</button>
				{/if}
			</div>
		</div>
	</div>
{/strip}
