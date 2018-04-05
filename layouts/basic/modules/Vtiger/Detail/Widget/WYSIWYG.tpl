{strip}
	<div class="c-detail-widget mb-3 js-detail-widget c-detail-widget--wysiwyg">
		<div class="c-detail-widget__header js-detail-widget-header">
			<div class="form-row align-items-center"><h4>{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h4></div>
		</div>
		<hr class="widgetHr">
		<div class="defaultMarginP">
			{assign var=FULL_TEXT value=$RECORD->getDisplayValue($WIDGET['data']['field_name'])}
			{assign var=TRUNCATE_TEXT value=\App\TextParser::htmlTruncate($FULL_TEXT,600)}
			<div class="moreContent table-responsive">
				<span class="teaserContent">
					{$TRUNCATE_TEXT}
				</span>
				{if $FULL_TEXT != $TRUNCATE_TEXT}
					<span class="fullContent d-none">
						{$FULL_TEXT}
					</span>
					<button type="button" class="btn btn-info btn-sm moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
				{/if}
			</div>
		</div>
	</div>
{/strip}
