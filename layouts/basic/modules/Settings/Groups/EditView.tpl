{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<div class="editViewContainer">
		<form name="EditGroup" action="index.php" method="post" id="EditView" class="form-horizontal">
			<input type="hidden" name="module" value="Groups">
			<input type="hidden" name="action" value="Save">
			<input type="hidden" name="parent" value="Settings">
			<input type="hidden" name="record" value="{$RECORD_MODEL->getId()}">
			<input type="hidden" name="mode" value="{$MODE}">

			<div class="widget_header row">
				<div class="col-xs-12">
					{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
					{if isset($SELECTED_PAGE)}
						{vtranslate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
					{/if}
				</div>
			</div>
			<hr>
			<div class="form-group">
				<div class="col-md-2 description-field">
					<span class="redColor">*</span> {vtranslate('LBL_GROUP_NAME', $QUALIFIED_MODULE)}
				</div>
				<div class="col-md-6 controls">
					<input class="form-control" name="groupname" value="{$RECORD_MODEL->getName()}" data-validation-engine="validate[required]">
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-2 description-field">
					{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
				</div>
				<div class="col-md-6 controls">
					<input class="form-control" name="description" id="description" value="{$RECORD_MODEL->getDescription()}" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-2 description-field">
					<span class="redColor">*</span> {vtranslate('LBL_MODULES', $QUALIFIED_MODULE)}
				</div>
				<div class="col-md-6 controls">
					<select id="modulesList" class="row modules select2 form-control" multiple="true" name="modules[]" data-validation-engine="validate[required]">
						{foreach from=Vtiger_Module_Model::getAll([0],[],true) key=TABID item=MODULE_MODEL}
							<option value="{$TABID}" {if array_key_exists($TABID, $RECORD_MODEL->getModules())}selected="true"{/if}>{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-2 description-field">
					<span class="redColor">*</span> {vtranslate('LBL_GROUP_MEMBERS', $QUALIFIED_MODULE)}
				</div>
				<div class="col-md-6 controls">
					<div class="row">
						<div class="col-md-6">
							{assign var="GROUP_MEMBERS" value=$RECORD_MODEL->getMembers()}
							<select id="memberList" class="members form-control select2 groupMembersColors" multiple="true" name="members[]" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}" data-validation-engine="validate[required]">
								{foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
									<optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}">
										{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
											{if $MEMBER->getName() neq $RECORD_MODEL->getName()}
												<option class="{$GROUP_LABEL}" value="{$MEMBER->getId()}"  data-member-type="{$GROUP_LABEL}" {if isset($GROUP_MEMBERS[$GROUP_LABEL][$MEMBER->getId()])}selected="true"{/if}>{vtranslate($MEMBER->getName(), $QUALIFIED_MODULE)}</option>
											{/if}
										{/foreach}
									</optgroup>
								{/foreach}
							</select>
						</div>
						<div class="col-md-2">
							<span class="pull-right groupMembersColors">
								<ul class="liStyleNone">
									<li class="Users padding5per textAlignCenter"><strong>{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}</strong></li>
									<li class="Groups padding5per textAlignCenter"><strong>{vtranslate('LBL_GROUPS', $QUALIFIED_MODULE)}</strong></li>
									<li class="Roles padding5per textAlignCenter"><strong>{vtranslate('LBL_ROLES', $QUALIFIED_MODULE)}</strong></li>
									<li class="RoleAndSubordinates padding5per textAlignCenter"><strong>{vtranslate('LBL_ROLEANDSUBORDINATE', $QUALIFIED_MODULE)}</strong></li>
								</ul>
							</span>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-5 pull-right">
					<span class="pull-right">
						<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
						<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
					</span>
				</div>
			</div>
		</form>
	</div>
{/strip}
