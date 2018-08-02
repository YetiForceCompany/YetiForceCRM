{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<ul>
		{foreach from=$ROLE->getChildren() item=CHILD_ROLE}
			<li data-role="{$CHILD_ROLE->getParentRoleString()}" data-roleid="{$CHILD_ROLE->getId()}">
				<div class="toolbar-handle">
					{if $TYPE == 'Transfer'}
						{assign var="SOURCE_ROLE_SUBPATTERN" value='::'|cat:$SOURCE_ROLE->getId()}
						{if strpos($CHILD_ROLE->getParentRoleString(), $SOURCE_ROLE_SUBPATTERN) !== false}
							<a href="{$CHILD_ROLE->getEditViewUrl()}" data-url="{$CHILD_ROLE->getEditViewUrl()}" class="btn btn-info" rel="tooltip" >{\App\Language::translate($CHILD_ROLE->getName(),$QUALIFIED_MODULE)}</a>
						{else}
							<a href="{$CHILD_ROLE->getEditViewUrl()}" data-url="{$CHILD_ROLE->getEditViewUrl()}" class="btn btn-light roleEle" rel="tooltip" >{\App\Language::translate($CHILD_ROLE->getName(),$QUALIFIED_MODULE)}</a>
						{/if}
					{else}
						<a href="{$CHILD_ROLE->getEditViewUrl()}" data-url="{$CHILD_ROLE->getEditViewUrl()}" class="btn btn-light draggable droppable" rel="tooltip" title="{\App\Language::translate('LBL_CLICK_TO_EDIT_OR_DRAG_TO_MOVE',$QUALIFIED_MODULE)}">{\App\Language::translate($CHILD_ROLE->getName(),$QUALIFIED_MODULE)}</a>
					{/if}
					{if $VIEW != 'Popup'}
						<div class="toolbar">
							&nbsp;<a href="{$CHILD_ROLE->getCreateChildUrl()}" data-url="{$CHILD_ROLE->getCreateChildUrl()}" title="{\App\Language::translate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}"><span class="fas fa-plus-circle"></span></a>
							&nbsp;<a data-id="{$CHILD_ROLE->getId()}" href="javascript:;" data-url="{$CHILD_ROLE->getDeleteActionUrl()}" data-action="modal" title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"><span class="fas fa-trash-alt"></span></a>
						</div>
					{/if}
				</div>

				{assign var="ROLE" value=$CHILD_ROLE}
				{include file=\App\Layout::getTemplatePath('RoleTree.tpl', 'Settings:Roles')}
			</li>
		{/foreach}
	</ul>
{/strip}
