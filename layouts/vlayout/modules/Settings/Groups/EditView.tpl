{*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************}
{strip}
<div class="editViewContainer container-fluid">
	<form name="EditGroup" action="index.php" method="post" id="EditView" class="form-horizontal">
		<input type="hidden" name="module" value="Groups">
		<input type="hidden" name="action" value="Save">
		<input type="hidden" name="parent" value="Settings">
		<input type="hidden" name="record" value="{$RECORD_MODEL->getId()}">
		<input type="hidden" name="mode" value="{$MODE}">
		
		<div class="contentHeader row-fluid">
			<h3> 
				{if !empty($MODE)}
					{vtranslate('LBL_EDITING', $QUALIFIED_MODULE)} {vtranslate('SINGLE_'|cat:$MODULE, $QUALIFIED_MODULE)} - {$RECORD_MODEL->getName()}
				{else}
					{vtranslate('LBL_CREATING_NEW', $QUALIFIED_MODULE)} {vtranslate('SINGLE_'|cat:$MODULE, $QUALIFIED_MODULE)}
				{/if}
			</h3>
            <hr>
		</div>
		<div class="control-group">
			<span class="control-label">
				<span class="redColor">*</span> {vtranslate('LBL_GROUP_NAME', $QUALIFIED_MODULE)}
			</span>
			<div class="controls">
				<input class="input-large" name="groupname" value="{$RECORD_MODEL->getName()}" data-validation-engine="validate[required]">
			</div>
		</div>
		<div class="control-group">
			<span class="control-label">
				{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
			</span>
			<div class="controls">
				<input class="input-large" name="description" id="description" value="{$RECORD_MODEL->getDescription()}" />
			</div>
		</div>
		<div class="control-group">
			<span class="control-label">
				{vtranslate('LBL_GROUP_MEMBERS', $QUALIFIED_MODULE)}
			</span>
			<div class="controls">
				<div class="row-fluid">
					<span class="span6">
						{assign var="GROUP_MEMBERS" value=$RECORD_MODEL->getMembers()}
						<select id="memberList" class="row-fluid members" multiple="true" name="members[]" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}" data-validation-engine="validate[required]">
							{foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
								<optgroup label="{$GROUP_LABEL}">
								{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
									{if $MEMBER->getName() neq $RECORD_MODEL->getName()}
										<option value="{$MEMBER->getId()}"  data-member-type="{$GROUP_LABEL}" {if isset($GROUP_MEMBERS[$GROUP_LABEL][$MEMBER->getId()])}selected="true"{/if}>{$MEMBER->getName()}</option>
									{/if}
								{/foreach}
								</optgroup>
							{/foreach}
						</select>
					</span>
					<span class="span3">
						<span class="pull-right groupMembersColors">
							<ul class="liStyleNone">
								<li class="Users padding5per textAlignCenter"><strong>{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}</strong></li>
								<li class="Groups padding5per textAlignCenter"><strong>{vtranslate('LBL_GROUPS', $QUALIFIED_MODULE)}</strong></li>
								<li class="Roles padding5per textAlignCenter"><strong>{vtranslate('LBL_ROLES', $QUALIFIED_MODULE)}</strong></li>
								<li class="RoleAndSubordinates padding5per textAlignCenter"><strong>{vtranslate('LBL_ROLEANDSUBORDINATE', $QUALIFIED_MODULE)}</strong></li>
							</ul>
						</span>
					</span>
				</div>
			</div>
		</div>
			<div class="row-fluid">
				<div class="span5">
					<span class="pull-right">
						<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
						<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
					</span>
				</div>
			</div>
	</form>
</div>
{/strip}