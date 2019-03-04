{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-WYSIWYG c-detail-widget u-mb-13px js-detail-widget c-detail-widget--wysiwyg"
		 data-js="container">
		<div>
			<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
				<h5 class="mb-0 py-2">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
				<hr class="widgetHr">
			</div>
		</div>
		<div class="m-2">
			{assign var=FULL_TEXT value=$RECORD->getDisplayValue($WIDGET['data']['field_name'])}
			{assign var=TRUNCATE_TEXT value=\App\TextParser::htmlTruncate($FULL_TEXT,600,true,$IS_TRUNCATED)}
			<div class="moreContent table-responsive">
				<span class="teaserContent">
					{$TRUNCATE_TEXT}
				</span>
				{if $IS_TRUNCATED}
					<span class="fullContent d-none">
						{$FULL_TEXT}
					</span>
					<button type="button" class="btn btn-info btn-sm moreBtn"
							data-on="{\App\Language::translate('LBL_MORE_BTN')}"
							data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
				{/if}
			</div>
		</div>
	</div>
{/strip}
