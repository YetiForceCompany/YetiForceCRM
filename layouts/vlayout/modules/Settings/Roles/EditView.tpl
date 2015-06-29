{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<div class="">
		<br>
		<h3 class="themeTextColor">{vtranslate($MODULE, $QUALIFIED_MODULE)}</h3>
		<hr>

		<form name="EditRole" action="index.php" method="post" id="EditView" class="form-horizontal">
			<input type="hidden" name="module" value="Roles">
			<input type="hidden" name="action" value="Save">
			<input type="hidden" name="parent" value="Settings">
			{assign var=RECORD_ID value=$RECORD_MODEL->getId()}
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" name="mode" value="{$MODE}">
			<input type="hidden" name="profile_directly_related_to_role_id" value="{$PROFILE_ID}" />
			{assign var=HAS_PARENT value="{if $RECORD_MODEL->getParent()}true{/if}"}
			{if $HAS_PARENT}
				<input type="hidden" name="parent_roleid" value="{$RECORD_MODEL->getParent()->getId()}">
			{/if}

			<div style="padding:20px;">
				<div class="row">
					<div class="col-md-3">
						<label class=""><strong>{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}<span class="redColor">*</span>: </strong></label>
					</div>
					<div class=" col-md-7 ">
						<input type="text" class="fieldValue form-control" name="rolename" id="profilename" value="{$RECORD_MODEL->getName()}" data-validation-engine='validate[required]'  />
					</div>
				</div><br>
				<div class="row">
					<label class="col-md-3"><strong>{vtranslate('LBL_REPORTS_TO', $QUALIFIED_MODULE)}: </strong></label>
					<div class="col-md-7 fieldValue">
						<input type="hidden" name="parent_roleid" {if $HAS_PARENT}value="{$RECORD_MODEL->getParent()->getId()}"{/if}>
						<input type="text" class="form-control" name="parent_roleid_display" {if $HAS_PARENT}value="{vtranslate($RECORD_MODEL->getParent()->getName(), $QUALIFIED_MODULE)}"{/if} readonly>
					</div>
				</div><br>
                <div class="row">
					<label class="col-md-3"><strong>{vtranslate('LBL_CAN_ASSIGN_RECORDS_TO', $QUALIFIED_MODULE)}: </strong></label>
					<div class="col-md-7 fieldValue">
						<div>
							<label for="allow1">
								<input type="radio" id="allow1" value="1"{if !$RECORD_MODEL->get('allowassignedrecordsto')} checked=""{/if} {if $RECORD_MODEL->get('allowassignedrecordsto') eq '1'} checked="" {/if} name="allowassignedrecordsto" data-handler="new" class="alignTop"/>&nbsp;
								{vtranslate('LBL_ALL_USERS',$QUALIFIED_MODULE)}
							</label>
						</div>
						<div>
							<label for="allow2">
								<input type="radio" id="allow2" value="2" {if $RECORD_MODEL->get('allowassignedrecordsto') eq '2'} checked="" {/if} name="allowassignedrecordsto" data-handler="new" class="alignTop"/>&nbsp;
								{vtranslate('LBL_USERS_WITH_SAME_OR_LOWER_LEVEL',$QUALIFIED_MODULE)}
							</label>
						</div>
                        <div>
							<label for="allow3">
								<input type="radio" id="allow3" value="3" {if $RECORD_MODEL->get('allowassignedrecordsto') eq '3'} checked="" {/if} name="allowassignedrecordsto" data-handler="new" class="alignTop"/>&nbsp;
								{vtranslate('LBL_USERS_WITH_LOWER_LEVEL',$QUALIFIED_MODULE)}
							</label>
						</div>
                        <div>
							<label for="allow4">
								<input type="radio" id="allow4" value="4" {if $RECORD_MODEL->get('allowassignedrecordsto') eq '4'} checked="" {/if} name="allowassignedrecordsto" data-handler="new" class="alignTop"/>&nbsp;
								{vtranslate('LBL_JUST_ME',$QUALIFIED_MODULE)}
							</label>
						</div>
				</div>
                </div><br>
				<div class="row">
					<label class="col-md-3"><strong>{vtranslate('LBL_PRIVILEGES',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						<div class="pull-left">
							<input type="radio" value="1" {if $PROFILE_DIRECTLY_RELATED_TO_ROLE} checked="" {/if} name="profile_directly_related_to_role" data-handler="new" class="alignTop"/>&nbsp;<span>{vtranslate('LBL_ASSIGN_NEW_PRIVILEGES',$QUALIFIED_MODULE)}</span>
						</div>
						<div class="pull-right">
							<input type="radio" value="0" {if $PROFILE_DIRECTLY_RELATED_TO_ROLE eq false} checked="" {/if} name="profile_directly_related_to_role" data-handler="existing" class="alignTop"/>&nbsp;<span>{vtranslate('LBL_ASSIGN_EXISTING_PRIVILEGES',$QUALIFIED_MODULE)}</span>
						</div>
					</div>
				</div>
				<br>
				<div class="row padding20px boxSizingBorderBox contentsBackground" data-content-role="new" style="display: none">
					<div class="fieldValue col-md-12">
					</div>
				</div>
				<div class="" data-content-role="existing" style="display: none">
					<div class="fieldValue">
						{assign var="ROLE_PROFILES" value=$RECORD_MODEL->getProfiles()}
						<select class="select2" multiple="true" id="profilesList" name="profiles[]" data-placeholder="{vtranslate('LBL_CHOOSE_PROFILES',$QUALIFIED_MODULE)}" style="width: 800px">
							{foreach from=$ALL_PROFILES item=PROFILE}
								{if $PROFILE->isDirectlyRelated() eq false}
									<option value="{$PROFILE->getId()}" {if isset($ROLE_PROFILES[$PROFILE->getId()])}selected="true"{/if}>{vtranslate($PROFILE->getName(),'Profiles')}</option>
								{/if}
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="textAlignCenter">
				<button class="btn btn-success" type="submit">{vtranslate('LBL_SAVE',$MODULE)}</button>
				<a class="cancelLink" onclick="javascript:window.history.back();" type="reset">{vtranslate('LBL_CANCEL',$MODULE)}</a>
			</div>
			{if count($ROLE_USERS) > 0 }
				<hr />
				<h4>{vtranslate('LBL_USERS_LIST',$QUALIFIED_MODULE)}</h4>
				<br />
				<table class="table table-striped">
					<thead>
						<tr>
							<th>{vtranslate('User Name','Users')}</th>
							<th>{vtranslate('First Name','Users')}</th>
							<th>{vtranslate('Last Name','Users')}</th>
							<th>{vtranslate('Email','Users')}</th>
							<th>{vtranslate('Status','Users')}</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$ROLE_USERS key=key item=USER}
							<tr>
								<td>{$USER->get('user_name')}</td>
								<td>{$USER->get('first_name')}</td>
								<td>{$USER->get('last_name')}</td>
								<td>{$USER->get('email1')}</td>
								<td>{vtranslate($USER->get('status'),'Users')}</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			{/if}
		</form>
	</div>
{/strip}
