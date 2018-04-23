{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	{include file=\App\Layout::getTemplatePath('EditViewBlocks.tpl', 'Vtiger')}
    <input type="hidden" name="userChangedEndDateTime" value="{$USER_CHANGED_END_DATE_TIME}" />
	<div class="js-toggle-panel c-panel c-panel--edit row mx-1 mb-3" data-js="click" data-label="{$BLOCK_LABEL}">
		<div class="blockHeader c-panel__header align-items-center">
			<div class="col-md-8 form-row pl-1">
				<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2 {if !($IS_HIDDEN)}d-none{/if}" data-js="click" data-mode="hide"></span>
				<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2 {if ($IS_HIDDEN)}d-none{/if}" data-js="click" data-mode="show"></span>
				<h5>{\App\Language::translate('LBL_INVITE_RECORDS', $MODULE)}</h5>
			</div>
			<div class="col-md-4 fieldRow">
				<input type="text" title="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE)}" placeholder="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE)}" class="form-control form-control-sm inviteesSearch" />
			</div>
		</div>
		<div class="c-panel__body c-panel__body--edit blockContent js-block-content {if $IS_HIDDEN}d-none{/if}" data-js="display">
			<div class="inviteesContent">
				<div class="d-none">
					{include file=\App\Layout::getTemplatePath('InviteRow.tpl', $MODULE)}
				</div>
				{foreach key=KEY item=INVITIE from=$INVITIES_SELECTED}
					{include file=\App\Layout::getTemplatePath('InviteRow.tpl', $MODULE)}
				{/foreach}
			</div>
		</div>
	</div>
	<br />
{/strip}
