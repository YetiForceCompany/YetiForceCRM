{strip}
	<div class="tpl-Vtiger-Detail-WYSIWYG c-detail-widget u-mb-13px js-detail-widget c-detail-widget--wysiwyg" data-js=”container”>
		<div class="c-detail-widget__header js-detail-widget-header" data-js=”container|value>
			<h5 class="mb-0 py-2">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
			<hr class="widgetHr">
		</div>
		<div class="m-5">
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
