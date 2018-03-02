{strip}
	<div class="summaryWidgetContainer">
		<div class="widget_header row">
			<span class="col-md-10 margin0px"><h4>{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h4></span>
		</div>
		<div class="defaultMarginP">
			{assign var=FULL_TEXT value=$RECORD->getDisplayValue($WIDGET['data']['field_name'])}
			<div class="moreContent table-responsive">
				<span class="teaserContent">
					{$FULL_TEXT|substr:0:600}
				</span>
				{if $FULL_TEXT|strlen > 600}
					<span class="fullContent d-none">
						{$FULL_TEXT}
					</span>
					<button type="button" class="btn btn-info btn-sm moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
				{/if}
			</div>
		</div>
	</div>
{/strip}
