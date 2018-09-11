{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if $HEADER_TYPE eq 'value' && (isset($FIELDS_HEADER[$HEADER_TYPE]) || $CUSTOM_FIELDS_HEADER)}
		<div class="tpl-Detail-HeaderFields ml-md-2 pr-md-2 u-min-w-md-30 w-100">
			{if $CUSTOM_FIELDS_HEADER}
				{foreach from=$CUSTOM_FIELDS_HEADER item=ROW}
					<div class="badge badge-info d-flex flex-nowrap align-items-center justify-content-center my-1 js-popover-tooltip" data-ellipsis="true" data-content="{$ROW['title']} {$ROW['badge']}" data-toggle="popover" data-js="tooltip"
						 {if $ROW['action']}onclick="{\App\Purifier::encodeHtml($ROW['action'])}"{/if}>
						<div class="c-popover-text">
							<span class="mr-1">{$ROW['title']}</span>
							{$ROW['badge']}
						</div>
						<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					</div>
				{/foreach}
			{/if}
			{if isset($FIELDS_HEADER[$HEADER_TYPE])}
				{foreach from=$FIELDS_HEADER[$HEADER_TYPE] key=NAME item=FIELD_MODEL}
					{if !$RECORD->isEmpty($NAME)}
						{assign var=VALUE value=$RECORD->getDisplayValue($NAME)}
						<div class="badge {if $FIELD_MODEL->getHeaderValue('header_class')}{$FIELD_MODEL->getHeaderValue('header_class')}{else}badge-info{/if} d-flex flex-nowrap align-items-center justify-content-center mt-1 js-popover-tooltip" data-ellipsis="true"
							 data-content='{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE_NAME)}: <string>{$VALUE}</string>' data-toggle="popover" data-js="tooltip">
							<div class="c-popover-text">
								<span class="mr-1">
									{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE_NAME)}:
								</span>
								{$VALUE}
							</div>
							<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
						</div>
					{/if}
				{/foreach}
			{/if}
		</div>
	{elseif $HEADER_TYPE eq 'process' && isset($FIELDS_HEADER[$HEADER_TYPE])}
		{foreach from=$FIELDS_HEADER['process'] key=NAME item=FIELD_MODEL}
			{if !$RECORD->isEmpty($NAME)}
				{assign var=PICKLIST_VALUES value=\App\Fields\Picklist::getValues($NAME)}
				<div class="c-arrows px-3 w-100">
					<ul class="c-arrows__container">
						{assign var=ARROW_CLASS value="before"}
						{foreach from=$PICKLIST_VALUES item=VALUE_DATA name=picklistValues}
							<li class="c-arrows__item {if $smarty.foreach.picklistValues.first}first{/if} {if $VALUE_DATA['picklistValue'] eq $RECORD->get($NAME)}active{assign var=ARROW_CLASS value="after"}{else}{$ARROW_CLASS}{/if}">
								<a class="c-arrows__link">
									<span class="c-arrows__text">{$FIELD_MODEL->getDisplayValue($VALUE_DATA['picklistValue'], false, false, true)}</span>
								</a>
							</li>
						{/foreach}
					</ul>
				</div>
			{/if}
		{/foreach}
	{/if}
{/strip}
