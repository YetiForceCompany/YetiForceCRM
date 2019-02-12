{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-HeaderProgress -->
	{if isset($FIELDS_HEADER['progress'])}
		{assign var=CLOSE_STATES value=\App\Fields\Picklist::getCloseStates($MODULE_MODEL->getId(), false)}
		{foreach from=$FIELDS_HEADER['progress'] key=NAME item=FIELD_MODEL}
			{if !$RECORD->isEmpty($NAME)}
				{assign var=PICKLIST_OF_FIELD value=$FIELD_MODEL->getPicklistValues()}
				{assign var=PICKLIST_VALUES value=\App\Fields\Picklist::getValues($NAME)}
				{assign var=IS_EDITABLE value=$RECORD->isEditable() && $FIELD_MODEL->isAjaxEditable() && !$FIELD_MODEL->isEditableReadOnly()}
				<div class="c-progress px-3 w-100">
					<ul class="c-progress__container js-header-progress-bar list-inline my-0 py-1 js-scrollbar c-scrollbar-x--small" data-picklist-name="{$NAME}"
						data-js="container">
						{assign var=ARROW_CLASS value="before"}
						{assign var=ICON_CLASS value="fas fa-check"}
						{foreach from=$PICKLIST_VALUES item=VALUE_DATA name=picklistValues}
							{assign var=IS_ACTIVE value=$VALUE_DATA['picklistValue'] eq $RECORD->get($NAME)}
							{assign var=IS_LOCKED value=isset($CLOSE_STATES[$VALUE_DATA['picklist_valueid']])}
							{assign var=PICKLIST_LABEL value=$FIELD_MODEL->getDisplayValue($VALUE_DATA['picklistValue'], false, false, true)}
							<li class="c-progress__item list-inline-item mx-0 {if $smarty.foreach.picklistValues.first}first{/if} {if $IS_ACTIVE}active{assign var=ARROW_CLASS value="after"}{else}{$ARROW_CLASS}{/if}{if $IS_EDITABLE && $VALUE_DATA['picklistValue'] !== $RECORD->get($NAME) && isset($PICKLIST_OF_FIELD[$VALUE_DATA['picklistValue']])} u-cursor-pointer js-access{/if}"
								data-picklist-value="{$VALUE_DATA['picklistValue']}"
								data-picklist-label="{\App\Purifier::encodeHtml($PICKLIST_LABEL)}"
								data-js="confirm|click|data">
								<div class="c-progress__icon__container">
									<span class="
								{if $IS_LOCKED}
									fas fa-lock
								{elseif $IS_ACTIVE}
									far fa-dot-circle
								{else}
									{$ICON_CLASS}
								{/if}
								{if $IS_ACTIVE}
									{assign var=ICON_CLASS value="c-progress__icon__dot"}
								{/if}
								  {' '}c-progress__icon"></span>
								</div>
								<div class="c-progress__link">
									{if !empty($VALUE_DATA['description'])}
										<span class="c-progress__icon-info js-popover-tooltip"
											  data-js="popover"
											  data-trigger="hover focus"
											  data-content="{\App\Purifier::encodeHtml($VALUE_DATA['description'])}">
																			<span class="fas fa-info-circle"></span>
									</span>
									{/if}
									<span class=" js-popover-tooltip--ellipsis" data-toggle="popover" data-content="{$PICKLIST_LABEL}" data-js="popover">
									<span class="c-progress__text">{$PICKLIST_LABEL}</span>
									</span>
								</div>

							</li>
						{/foreach}
					</ul>
				</div>
			{/if}
		{/foreach}
	{/if}
	<!-- /tpl-Base-Detail-HeaderProgress -->
{/strip}
