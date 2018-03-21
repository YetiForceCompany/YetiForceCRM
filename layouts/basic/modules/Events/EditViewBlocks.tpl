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
	<div class="js-toggle-panel c-panel__content row blockContainer mx-1" data-js="click" data-label="{$BLOCK_LABEL}">
		<div class="blockHeader card-header bg-light">
			<div class="col-md-8 form-row">
				<span class="cursorPointer blockToggle fas fa-angle-right {if !($IS_HIDDEN)}d-none{/if}" data-mode="hide"></span>
				<span class="cursorPointer blockToggle fas fa-angle-down {if ($IS_HIDDEN)}d-none{/if}" data-mode="show"></span>
				<h4>{\App\Language::translate('LBL_INVITE_RECORDS', $MODULE)}</h4>
			</div>
			<div class="col-md-4 fieldRow">
				<input type="text" title="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE)}" placeholder="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE)}" class="form-control inviteesSearch" />
			</div>
		</div>
		<div class="col-md-12 paddingLRZero panel-body blockContent js-block-content {if $IS_HIDDEN}d-none{/if}" data-js="display">
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
