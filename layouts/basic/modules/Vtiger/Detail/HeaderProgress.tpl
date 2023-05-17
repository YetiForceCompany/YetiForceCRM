{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-HeaderProgress -->
	{function SHOW_PROGRESS_HEADER PROGRESS_HEADER='' TYPE=''}
		{if isset($PROGRESS_HEADER)}
			{foreach from=$PROGRESS_HEADER key=NAME item=FIELD_MODEL}
				{if !$RECORD->isEmpty($NAME)}
					<div class="c-progress px-3 w-100">
						<ul class="c-progress__container js-header-progress-bar list-inline my-0 py-1 js-scrollbar c-scrollbar-x--small" data-picklist-name="{$NAME}"
							data-js="container">
							{assign var=ARROW_CLASS value="before"}
							{foreach from=$FIELD_MODEL->getUITypeModel()->getProgressHeader($RECORD) key=PROGRESS_HEADER_KEY item=PROGRESS_HEADER_VALUE name=progressHeaderValue}
								{if $PROGRESS_HEADER_VALUE['isLocked']}
									{assign var=ICON_CLASS value="fas fa-lock"}
								{elseif $PROGRESS_HEADER_VALUE['isActive'] && $TYPE eq 'PROGRESS'}
									{assign var=ICON_CLASS value="far fa-dot-circle"}
								{elseif $PROGRESS_HEADER_VALUE['isActive'] && $TYPE eq 'SELECTION_BAR'}
									{assign var=ICON_CLASS value="fas fa-check"}
								{else}
									{if  $TYPE eq 'PROGRESS' && $ARROW_CLASS eq 'before'}
										{assign var=ICON_CLASS value="fas fa-check"}
									{else}
										{assign var=ICON_CLASS value="c-progress__icon__dot"}
									{/if}
								{/if}
								<li class="c-progress__item {if $TYPE eq 'SELECTION_BAR'}c-progress__item--select{/if} list-inline-item mx-0 {if $smarty.foreach.progressHeaderValue.first}first{/if} {if $PROGRESS_HEADER_VALUE['isActive']}active{assign var=ARROW_CLASS value="after"}{else}{$ARROW_CLASS}{/if}{if $PROGRESS_HEADER_VALUE['isEditable'] && $PROGRESS_HEADER_KEY !== $RECORD->get($NAME)} u-cursor-pointer js-access{/if}" data-picklist-value="{$PROGRESS_HEADER_KEY}" data-picklist-label="{\App\Purifier::encodeHtml($PROGRESS_HEADER_VALUE['label'])}" data-js="confirm|click|data">
									<div class="c-progress__icon__container">
										<span class="{$ICON_CLASS}
								{' '}c-progress__icon"></span>
									</div>
									<div class="c-progress__link">
										{if !empty($PROGRESS_HEADER_VALUE['description'])}
											<span class="c-progress__icon-info js-popover-tooltip" data-js="popover" data-trigger="hover focus" data-content="{\App\Purifier::encodeHtml($PROGRESS_HEADER_VALUE['description'])}">
												<span class="fas fa-info-circle"></span>
											</span>
										{/if}
										<span class=" js-popover-tooltip--ellipsis" data-toggle="popover" data-content="{$PROGRESS_HEADER_VALUE['label']}" data-js="popover">
											<span class="c-progress__text">{$PROGRESS_HEADER_VALUE['label']}</span>
										</span>
									</div>
								</li>
							{/foreach}
						</ul>
					</div>
				{/if}
			{/foreach}
		{/if}
	{/function}
	{if isset($FIELDS_HEADER['progress'])}
		{SHOW_PROGRESS_HEADER PROGRESS_HEADER=$FIELDS_HEADER['progress'] TYPE='PROGRESS'}
	{/if}
	{if isset($FIELDS_HEADER['selectionBar'])}
		{SHOW_PROGRESS_HEADER PROGRESS_HEADER=$FIELDS_HEADER['selectionBar'] TYPE='SELECTION_BAR'}
	{/if}
	<!-- /tpl-Base-Detail-HeaderProgress -->
{/strip}
