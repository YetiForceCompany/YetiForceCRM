{*
<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-WYSIWYG -->
{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}"}
<div class="tpl-Base-Detail-WYSIWYG c-detail-widget js-detail-widget c-detail-widget--wysiwyg" data-js="container">
	<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
		<div class="c-detail-widget__header__container d-flex align-items-center py-1">
			<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
				data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
				<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
			</div>
			<div class="c-detail-widget__header__title">
				<h5 class="mb-0 modCT_{$WIDGET['label']}" title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}">
					{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
			</div>
		</div>
	</div>
	<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse pt-1"
		id="{$WIDGET_UID}-collapse" data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}"
		data-js="container|value">
		<div class="mt-0 mx-0 moreContent">
			{assign var=FULL_TEXT value=$RECORD->getDisplayValue($WIDGET['data']['field_name'])}
			{assign var=TRUNCATE_TEXT value=\App\TextParser::htmlTruncate($FULL_TEXT,600,true,$IS_TRUNCATED)}
			<div class="table-responsive">
				<span class="teaserContent">
					{$TRUNCATE_TEXT}
				</span>
				{if $IS_TRUNCATED}
				<span class="fullContent d-none">
					{$FULL_TEXT}
				</span>
				{/if}
			</div>
			{if $IS_TRUNCATED}
			<div class="my-1 text-right">
				<button type="button" class="btn btn-info btn-sm moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}"
					data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
			</div>
			{/if}
		</div>
	</div>
</div>
<!-- /tpl-Base-Detail-Widget-WYSIWYG -->
{/strip}
