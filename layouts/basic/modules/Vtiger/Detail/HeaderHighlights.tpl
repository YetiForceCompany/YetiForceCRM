{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-HeaderHighlights -->
	{if isset($FIELDS_HEADER['highlights']) || $CUSTOM_FIELDS_HEADER}
		{if $CUSTOM_FIELDS_HEADER}
			{foreach from=$CUSTOM_FIELDS_HEADER item=ROW}
				<div class="badge badge-info d-flex flex-nowrap align-items-center justify-content-center my-1 js-popover-tooltip--ellipsis"
					data-content="{\App\Purifier::encodeHtml($ROW['title'])} {\App\Purifier::encodeHtml($ROW['badge'])}" data-toggle="popover"
					data-js="popover | mouseenter"
					{if isset($ROW['action']) && $ROW['action']}onclick="{\App\Purifier::encodeHtml($ROW['action'])}" {/if}>
					<div class="c-popover-text">
						<span class="mr-1">{$ROW['title']}</span>
						{$ROW['badge']}
					</div>
					<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
				</div>
			{/foreach}
		{/if}
		{if isset($FIELDS_HEADER['highlights'])}
			{foreach from=$FIELDS_HEADER['highlights'] key=NAME item=FIELD_MODEL}
				{if !$RECORD->isEmpty($NAME)}
					{assign var=VALUE value=$RECORD->getDisplayValue($NAME)}
					<div class="badge {if $FIELD_MODEL->getHeaderValue('class')}{$FIELD_MODEL->getHeaderValue('class')}{else}badge-info{/if} d-flex flex-nowrap align-items-center justify-content-center mt-1 js-popover-tooltip--ellipsis"
						data-content="{\App\Purifier::encodeHtml(\App\Language::translate($FIELD_MODEL->get('label'), $MODULE_NAME))}: <string>{\App\Purifier::encodeHtml($VALUE)}</string>"
						data-toggle="popover" data-js="popover | mouseenter">
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
	<!-- /tpl-Base-Detail-HeaderHighlights -->
{/strip}
