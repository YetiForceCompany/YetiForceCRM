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
    {include file="EditViewBlocks.tpl"|@vtemplate_path:'Vtiger'}
    <input type="hidden" name="userChangedEndDateTime" value="{$USER_CHANGED_END_DATE_TIME}" />
	<div class="panel panel-default row marginLeftZero marginRightZero blockContainer" data-label="{$BLOCK_LABEL}">
		<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
			<div class="iconCollapse">
				<span class="cursorPointer blockToggle glyphicon glyphicon-menu-right {if !($IS_HIDDEN)}hide{/if}" data-mode="hide"></span>
				<span class="cursorPointer blockToggle glyphicon glyphicon glyphicon-menu-down {if ($IS_HIDDEN)}hide{/if}" data-mode="show"></span>
				<h4>{vtranslate('LBL_INVITE_USER_BLOCK', $MODULE)}</h4>
			</div>
		</div>
		<div class="col-md-12 paddingLRZero panel-body blockContent {if $IS_HIDDEN}hide{/if}">
			<div class="col-md-6 fieldRow">
				<div class="col-md-3 fieldLabel paddingLeft5px {$WIDTHTYPE}">
					<label class="muted pull-right marginRight10px">
						{vtranslate('LBL_INVITE_USERS', $MODULE)}
					</label>
				</div>
				<div class="{$WIDTHTYPE} col-md-9 fieldValue">
					<select id="selectedUsers" class="select2" multiple name="selectedusers[]" style="width:200px;" title="{vtranslate('LBL_INVITE_USERS', $MODULE)}">
						{foreach key=USER_ID item=USER_NAME from=$ACCESSIBLE_USERS}
							{if $USER_ID eq $CURRENT_USER->getId()}
								{continue}
							{/if}
							<option value="{$USER_ID}" title="{$USER_NAME}" {if in_array($USER_ID,$INVITIES_SELECTED)}selected{/if}>
								{$USER_NAME}
							</option>
						{/foreach}
					<select>
				</div>
			</div>
		</div>
	</div>
	<br>
{/strip}
