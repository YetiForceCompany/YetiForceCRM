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
				<div class="c-arrows px-3 w-100">
					<ul class="c-arrows__container js-header-progress-bar list-inline my-0 py-1 js-scrollbar c-scrollbar-x--small" data-picklist-name="{$NAME}" data-scrollbar-fn-name="showNewScrollbarTop"
						data-js="container">
						{assign var=ARROW_CLASS value="before"}
						{assign var=ICON_CLASS value="fas fa-check"}
						{assign var=ICON_SHRINK value="8"}
						{foreach from=$PICKLIST_VALUES item=VALUE_DATA name=picklistValues}
							{assign var=PICKLIST_LABEL value=$FIELD_MODEL->getDisplayValue($VALUE_DATA['picklistValue'], false, false, true)}
							<li class="c-arrows__item list-inline-item mx-0 {if $smarty.foreach.picklistValues.first}first{/if} {if $VALUE_DATA['picklistValue'] eq $RECORD->get($NAME)}active{assign var=ARROW_CLASS value="after"}{else}{$ARROW_CLASS}{/if}{if $IS_EDITABLE && $VALUE_DATA['picklistValue'] !== $RECORD->get($NAME) && isset($PICKLIST_OF_FIELD[$VALUE_DATA['picklistValue']])} u-cursor-pointer js-access{/if}"
								data-picklist-value="{$VALUE_DATA['picklistValue']}"
								data-picklist-label="{\App\Purifier::encodeHtml($PICKLIST_LABEL)}"
								data-js="confirm|click|data">

								<div class="c-arrows__icon__container flex-shrink-0 fa-layers fa-fw fa-2x">
									<span class="fas fa-circle c-arrows__icon-bg"></span>
									<span class="
								{if $VALUE_DATA['picklistValue'] eq $RECORD->get($NAME)}
									far fa-dot-circle
									{assign var=ICON_CLASS value="fas fa-circle"}
									{assign var=ICON_SHRINK value="4"}
								{elseif isset($CLOSE_STATES[$VALUE_DATA['picklist_valueid']])}
									fas fa-lock
								{else}
									{$ICON_CLASS}
								{/if}
								  {' '}c-arrows__icon" data-fa-transform="shrink-{if isset($CLOSE_STATES[$VALUE_DATA['picklist_valueid']])}7{else}{$ICON_SHRINK}{/if}"></span>
								</div>
								<a class="c-arrows__link js-popover-tooltip--ellipsis" data-toggle="popover" data-content="{$PICKLIST_LABEL}" data-js="popover">
									<span class="c-arrows__text">
									{$PICKLIST_LABEL}
									</span>
								</a>
							</li>
						{/foreach}
					</ul>
				</div>
			{/if}
		{/foreach}
	{/if}
	<!-- /tpl-Base-Detail-HeaderProgress -->
{/strip}
