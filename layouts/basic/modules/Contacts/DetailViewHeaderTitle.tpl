{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce Sp. z o.o
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
							<img class="js-detail-hierarchy" data-js="click" title="{$RECORD->getName()}" src="{$IMAGE.url}">
						{else}
							<span class="pl-0 o-detail__icon js-detail__icon js-detail-hierarchy userIcon-{$MODULE}" data-js="click"></span>
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
					{if $RECORD_STATE !== 'Active'}
						{assign var=COLOR value=App\Config::search('LIST_ENTITY_STATE_COLOR')}
						<span class="badge badge-secondary ml-1" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};"{/if}>
							{if \App\Record::getState($RECORD->getId()) === 'Trash'}
								{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
							{else}
								{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
							{/if}
						</span>
					{/if}
				</div>
			</div>
			<div class="pl-1">
				{$RECORD->getDisplayValue('parent_id')}
				<div class="js-popover-tooltip--ellipsis-icon d-flex flex-nowrap align-items-center" data-content="{\App\Purifier::encodeHtml($RECORD->getDisplayValue('assigned_user_id'))}" data-toggle="popover" data-js="popover | mouseenter">
					<span class="mr-1 text-muted u-white-space-nowrap">
						{\App\Language::translate('Assigned To',$MODULE_NAME)}:
					</span>
					<span class="js-popover-text" data-js="clone">{$RECORD->getDisplayValue('assigned_user_id')}</span>
					<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
				</div>
				{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
				{if $SHOWNERS != ''}
					<div class="js-popover-tooltip--ellipsis-icon d-flex flex-nowrap align-items-center" data-content="{\App\Purifier::encodeHtml($SHOWNERS)}" data-toggle="popover" data-js="popover | mouseenter">
						<span class="mr-1 text-muted u-white-space-nowrap">
							{\App\Language::translate('Share with users',$MODULE_NAME)}:
						</span>
						<span class="js-popover-text" data-js="clone">{$SHOWNERS}</span>
						<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					</div>
				{/if}
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Detail/HeaderFields.tpl', $MODULE_NAME)}
	</div>
	{include file=\App\Layout::getTemplatePath('Detail/HeaderProgress.tpl', $MODULE_NAME)}
{/strip}
