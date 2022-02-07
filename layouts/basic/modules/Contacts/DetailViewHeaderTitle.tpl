{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class="d-flex flex-wrap flex-md-nowrap px-md-3 px-1 w-100">
		<div class="u-min-w-md-70 w-100">
			<div>
				<div class="float-left spanModuleIcon moduleIcon{$MODULE_NAME}">
					{assign var=IMAGE value=$RECORD->getImage()}
					<span class="moduleIcon{if $IMAGE} o-detail__record-img mr-1{/if}">
						{if $IMAGE}
							<img class="js-detail-hierarchy rounded-circle" data-js="click" title="{$RECORD->getName()}" src="{$IMAGE['url']}">
						{else}
							<span class="pl-0 o-detail__icon js-detail__icon js-detail-hierarchy yfm-{$MODULE}" data-js="click"></span>
						{/if}
						{if App\Config::module($MODULE_NAME, 'COUNT_IN_HIERARCHY')}
							<span class="hierarchy">
								<span class="badge bgGreen"></span>
							</span>
						{/if}
					</span>
				</div>
				{assign var=SALUTATION value=''}
				{if $RECORD->getField('salutationtype')->isViewable()}
					{assign var=SALUTATION value=$RECORD->getDisplayValue('salutationtype')}
				{/if}
				<div class="d-flex flex-nowrap align-items-center js-popover-tooltip--ellipsis-icon" data-content="{if $SALUTATION}{\App\Purifier::encodeHtml($SALUTATION)} {/if}{\App\Purifier::encodeHtml($RECORD->getName())}"
					data-toggle="popover" data-js="popover | mouseenter">
					<h4 class="recordLabel h6 mb-0 js-popover-text" data-js="clone">
						{if $SALUTATION}
							<span class="salutation mr-1">{$SALUTATION}</span>
						{/if}
						<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
					</h4>
					<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					{assign var=RECORD_STATE value=\App\Record::getState($RECORD->getId())}
					{if $RECORD_STATE && $RECORD_STATE !== 'Active'}
						{assign var=COLOR value=App\Config::search('LIST_ENTITY_STATE_COLOR')}
						<span class="badge badge-secondary ml-1" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};" {/if}>
							{if \App\Record::getState($RECORD->getId()) === 'Trash'}
								{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
							{else}
								{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
							{/if}
						</span>
					{/if}
				</div>
			</div>
			{include file=\App\Layout::getTemplatePath('Detail/HeaderValues.tpl', $MODULE_NAME)}
		</div>
		<div class="ml-md-2 pr-md-2 u-min-w-md-30 w-100">
			{include file=\App\Layout::getTemplatePath('Detail/HeaderButtons.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('Detail/HeaderHighlights.tpl', $MODULE_NAME)}
		</div>
	</div>
	{include file=\App\Layout::getTemplatePath('Detail/HeaderProgress.tpl', $MODULE_NAME)}
{/strip}
