{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<div class="tpl-Settings-Groups-EditView editViewContainer">
		<form name="EditGroup" action="index.php" method="post" id="EditView" class="form-horizontal">
			<input type="hidden" name="module" value="Groups">
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="record" value="{$RECORD_MODEL->getId()}">
			<div class="widget_header row">
				<div class="col-12">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
					{if isset($SELECTED_PAGE)}
						{\App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
					{/if}
				</div>
			</div>
			<hr>
			<div class="form-group row">
				<div class="col-md-2 description-field">
					<span class="redColor">*</span> {\App\Language::translate('LBL_GROUP_NAME', $QUALIFIED_MODULE)}
				</div>
				<div class="col-md-6 controls">
					<input class="form-control" name="groupname" value="{$RECORD_MODEL->getName()}" data-validation-engine="validate[required]">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 description-field">
					{\App\Language::translate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
				</div>
				<div class="col-md-6 controls">
					<input class="form-control" name="description" id="description" value="{$RECORD_MODEL->getDescription()}" />
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 description-field">
					<span class="redColor">*</span> {\App\Language::translate('LBL_MODULES', $QUALIFIED_MODULE)}
				</div>
				<div class="col-md-6 controls">
					<select id="modulesList" class="row modules select2 form-control" multiple="true" name="modules[]" data-validation-engine="validate[required]">
						{foreach from=Vtiger_Module_Model::getAll([0],[],true) key=TABID item=MODULE_MODEL}
							<option value="{$TABID}" {if array_key_exists($TABID, $RECORD_MODEL->getModules())}selected="true"{/if}>{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 description-field">
					<span class="redColor">*</span> {\App\Language::translate('LBL_GROUP_MEMBERS', $QUALIFIED_MODULE)}
				</div>
				<div class="col-md-6 controls">
					<div class="row">
						<div class="col-md-6">
							{assign var="GROUP_MEMBERS" value=$RECORD_MODEL->getMembers()}
							<select id="memberList" class="members form-control select2 groupMembersColors" multiple="true" name="members[]" data-placeholder="{\App\Language::translate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}" data-validation-engine="validate[required]">
								{foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
									<optgroup label="{\App\Language::translate($GROUP_LABEL, $QUALIFIED_MODULE)}">
										{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
											{if $MEMBER->getName() neq $RECORD_MODEL->getName()}
												<option class="{$GROUP_LABEL}" value="{$MEMBER->getId()}"  data-member-type="{$GROUP_LABEL}" {if isset($GROUP_MEMBERS[$GROUP_LABEL][$MEMBER->getId()])}selected="true"{/if}>{\App\Language::translate($MEMBER->getName(), $QUALIFIED_MODULE)}</option>
											{/if}
										{/foreach}
									</optgroup>
								{/foreach}
							</select>
						</div>
						<div class="col-md-2">
							<span class="row float-right  groupMembersColors">
								<ul class="liStyleNone">
									<li class="Users text-center px-4"><strong>{\App\Language::translate('LBL_USERS', $QUALIFIED_MODULE)}</strong></li>
									<li class="Groups text-center px-4"><strong>{\App\Language::translate('LBL_GROUPS', $QUALIFIED_MODULE)}</strong></li>
									<li class="Roles text-center px-4"><strong>{\App\Language::translate('LBL_ROLES', $QUALIFIED_MODULE)}</strong></li>
									<li class="RoleAndSubordinates text-center px-4"><strong>{\App\Language::translate('LBL_ROLEANDSUBORDINATE', $QUALIFIED_MODULE)}</strong></li>
								</ul>
							</span>
						</div>
					</div>
				</div>
			</div>
			<div class="text-right">
				<button class="btn btn-success" type="submit"><span class="fas fa-check mr-1"></span><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
				<button class="btn btn-warning ml-2" type="reset" onclick="javascript:window.history.back();"><span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
			</div>
		</form>
	</div>
{/strip}
