{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-HeaderFields ml-md-2 pr-md-2 u-min-w-md-30 w-100">
		<div class="d-flex flex-nowrap align-items-end justify-content-end my-1 js-popover-tooltip">
			{if $DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL']}
				<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap d-block d-md-flex">
					{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL']}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewAdditional' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
					{/foreach}
				</div>
			{/if}
			{if $DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']}
				<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap d-block d-md-flex">
					{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewBasic' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
					{/foreach}
				</div>
			{/if}
			{if $DETAILVIEW_LINKS['DETAIL_VIEW_EXTENDED']}
				<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap d-block d-md-flex">
					{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_EXTENDED']}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewExtended' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
					{/foreach}
				</div>
			{/if}
		</div>

		{if isset($FIELDS_HEADER['value']) || $CUSTOM_FIELDS_HEADER}
			{if $CUSTOM_FIELDS_HEADER}
				{foreach from=$CUSTOM_FIELDS_HEADER item=ROW}
					<div class="badge badge-info d-flex flex-nowrap align-items-center justify-content-center my-1 js-popover-tooltip"
						 data-ellipsis="true" data-content="{$ROW['title']} {$ROW['badge']}" data-toggle="popover"
						 data-js="tooltip"
						 {if $ROW['action']}onclick="{\App\Purifier::encodeHtml($ROW['action'])}"{/if}>
						<div class="c-popover-text">
							<span class="mr-1">{$ROW['title']}</span>
							{$ROW['badge']}
						</div>
						<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					</div>
				{/foreach}
			{/if}
			{if isset($FIELDS_HEADER['value'])}
				{foreach from=$FIELDS_HEADER['value'] key=NAME item=FIELD_MODEL}
					{if !$RECORD->isEmpty($NAME)}
						{assign var=VALUE value=$RECORD->getDisplayValue($NAME)}
						<div class="badge {if $FIELD_MODEL->getHeaderValue('class')}{$FIELD_MODEL->getHeaderValue('class')}{else}badge-info{/if} d-flex flex-nowrap align-items-center justify-content-center mt-1 js-popover-tooltip"
							 data-ellipsis="true"
							 data-content='{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE_NAME)}: <string>{$VALUE}</string>'
							 data-toggle="popover" data-js="tooltip">
							<div class="c-popover-text">
								<span class="mr-1">
									{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE_NAME)}:
								</span>
								{$VALUE}
							</div>
							<span class="fas fa-info-circle fa-sm js-popover-icon d-none"
								  data-js="class: d-none"></span>
						</div>
					{/if}
				{/foreach}
			{/if}
		{/if}
	</div>
{/strip}
