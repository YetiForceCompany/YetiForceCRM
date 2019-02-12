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
	{if AppConfig::module('HelpDesk','CHECK_ACCOUNT_EXISTS') && $RECORD->get('parent_id') == 0}
		<div class="alert alert-danger w-100 mt-1 mb-2 mx-3" role="alert">
			<strong>{\App\Language::translate('LBL_NO_ACCOUNTS_IN_HELPDESK',{$MODULE})}</strong>
			<span class="text-right">
				<a href="javascript:HelpDesk_Detail_Js.setAccountsReference();">
					<strong> [ <span class="fas fa-search"></span> ]</strong>
				</a>
			</span>
		</div>
	{elseif AppConfig::module('HelpDesk','CHECK_SERVICE_CONTRACTS_EXISTS') && Vtiger_Module_Model::getInstance('ServiceContracts')->isActive() && $RECORD->get('servicecontractsid') == 0}
		{assign var=SERVICE_CONTRACTS value=$RECORD->getActiveServiceContracts()}
		<div class="alert {if $SERVICE_CONTRACTS}alert-warning{else}alert-danger{/if} selectServiceContracts w-100 mt-1 mb-2 mx-3 d-flex flex-column flex-sm-row justify-content-between u-overflow-x-hidden" role="alert">
			{if $SERVICE_CONTRACTS}
				<strong class="u-white-space-nowrap mr-2 align-self-center">{\App\Language::translate('LBL_NO_SERVICE_CONTRACTS_IN_HELPDESK',$MODULE)}</strong>
				<ul class="nav nav-pills flex-nowrap js-scrollbar" role="tablist" data-js="scroll">
					{foreach item=ROW from=$SERVICE_CONTRACTS}
						<li role="presentation" class="btn btn-light js-popover-tooltip  mr-1" data-js="popover" data-id="{$ROW['servicecontractsid']}" title="{$ROW['subject']}" data-content="{\App\Language::translate('LBL_SET_SERVICE_CONTRACTS_REFERENCE_DESC',$MODULE)}">
							<span class="fas fa-link"></span> {$ROW['subject']} {if $ROW['due_date']}({$ROW['due_date']}){/if}
						</li>
					{/foreach}
				</ul>
			{else}
				<strong>{\App\Language::translate('LBL_ACCOUNTS_NO_ACTIVE_SERVICE_CONTRACTS',$MODULE)}</strong>
			{/if}
		</div>
	{/if}
	<div class="d-flex flex-wrap flex-md-nowrap px-3 w-100">
		<div class="u-min-w-md-70 w-100">
			<div class="moduleIcon">
				<span class="o-detail__icon js-detail__icon userIcon-{$MODULE}"></span>
			</div>
			<div class="pl-1">
				<div class="d-flex flex-nowrap align-items-center js-popover-tooltip--ellipsis-icon" data-content="{\App\Purifier::encodeHtml($RECORD->getName())}" data-toggle="popover" data-js="popover | mouseenter">
					<h4 class="recordLabel h6 m-0 js-popover-text" data-js="clone">
						<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
					</h4>
					<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					{assign var=RECORD_STATE value=\App\Record::getState($RECORD->getId())}
					{if $RECORD_STATE !== 'Active'}
						{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
						<span class="badge badge-secondary ml-1" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};"{/if}>
							{if \App\Record::getState($RECORD->getId()) === 'Trash'}
								{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
							{else}
								{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
							{/if}
						</span>
					{/if}
				</div>
				{assign var=RELATED_TO value=$RECORD->get('parent_id')}
				{if !empty($RELATED_TO)}
					<div class="js-popover-tooltip--ellipsis-icon d-flex flex-nowrap align-items-center" data-js="popover | mouseenter">
						<span class="js-popover-text" data-js="clone">{$RECORD->getDisplayValue('parent_id')}</span>
						<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					</div>
				{/if}
				{assign var=PRIORITY value=$RECORD->get('ticketpriorities')}
				{if !empty($PRIORITY)}
					<div class="js-popover-tooltip--ellipsis-icon d-flex flex-nowrap align-items-center" data-content="{\App\Purifier::encodeHtml($RECORD->getDisplayValue('ticketpriorities'))}" data-toggle="popover" data-js="popover | mouseenter">
						<span class="text-muted">{\App\Language::translate('Priority',$MODULE_NAME)}: </span>
						<span class="js-popover-text">{$RECORD->getDisplayValue('ticketpriorities')}</span>
						<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>

					</div>
				{/if}
				{assign var=STATUS value=$RECORD->get('ticketstatus')}
				{if !empty($STATUS)}
					<div class="js-popover-tooltip--ellipsis-icon d-flex flex-nowrap align-items-center" data-content="{\App\Purifier::encodeHtml($RECORD->getDisplayValue('ticketstatus'))}" data-toggle="popover" data-js="popover | mouseenter">
						<span class="text-muted">{\App\Language::translate('Status',$MODULE_NAME)}: </span>
						<span class="js-popover-text">{$RECORD->getDisplayValue('ticketstatus')}</span>
						<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					</div>
				{/if}
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
