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
		<div class="row widget_header">
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				{if isset($SELECTED_PAGE)}
					{vtranslate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
				{/if}
			</div> 
		</div>
		<form name="EditRole" action="index.php" method="post" id="EditView" class="form-horizontal">
			<input type="hidden" name="module" value="Roles">
			<input type="hidden" name="action" value="Save">
			<input type="hidden" name="parent" value="Settings">
			{assign var=RECORD_ID value=$RECORD_MODEL->getId()}
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" name="mode" value="{$MODE}">
			{assign var=HAS_PARENT value="{if $RECORD_MODEL->getParent()}true{/if}"}
			{if $HAS_PARENT}
				<input type="hidden" name="parent_roleid" value="{$RECORD_MODEL->getParent()->getId()}">
			{/if}
			<div>
				<div class="row">
					<div class="col-md-4">
						<label class=""><span class="redColor">*</span><strong>{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}: </strong></label>
					</div>
					<div class=" col-md-7 ">
						<input type="text" class="fieldValue form-control" name="rolename" id="profilename" value="{$RECORD_MODEL->getName()}" data-validation-engine="validate[required]"/>
					</div>
				</div><br>
				<div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_REPORTS_TO', $QUALIFIED_MODULE)}: </strong></label>
					<div class="col-md-7 fieldValue">
						<input type="hidden" name="parent_roleid" {if $HAS_PARENT}value="{$RECORD_MODEL->getParent()->getId()}"{/if}>
						<input type="text" class="form-control" name="parent_roleid_display" {if $HAS_PARENT}value="{vtranslate($RECORD_MODEL->getParent()->getName(), $QUALIFIED_MODULE)}"{/if} readonly>
					</div>
				</div>
				<br>
                <div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_CAN_ASSIGN_OWNER_TO', $QUALIFIED_MODULE)}: </strong></label>
					<div class="col-md-7 fieldValue">
						<select id="allowassignedrecordsto" class="row select2 form-control" name="allowassignedrecordsto">
							<option value="1" {if $RECORD_MODEL->get('allowassignedrecordsto') == '1'}selected="true"{/if}>{vtranslate('LBL_ALL_USERS', $QUALIFIED_MODULE)}</option>
							<option value="2" {if $RECORD_MODEL->get('allowassignedrecordsto') == '2'}selected="true"{/if}>{vtranslate('LBL_USERS_WITH_SAME_OR_LOWER_LEVEL', $QUALIFIED_MODULE)}</option>
							<option value="3" {if $RECORD_MODEL->get('allowassignedrecordsto') == '3'}selected="true"{/if}>{vtranslate('LBL_USERS_WITH_LOWER_LEVEL', $QUALIFIED_MODULE)}</option>
							<option value="4" {if $RECORD_MODEL->get('allowassignedrecordsto') == '4'}selected="true"{/if}>{vtranslate('LBL_JUST_ME', $QUALIFIED_MODULE)}</option>
							<option value="5" {if $RECORD_MODEL->get('allowassignedrecordsto') == '5'}selected="true"{/if}>{vtranslate('LBL_FROM_PANEL', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
                </div>
				<br>
                <div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_CAN_ASSIGN_MULTIOWNER_TO', $QUALIFIED_MODULE)}: </strong></label>
					<div class="col-md-7 fieldValue">
						<select id="allowassignedrecordsto" class="row select2 form-control" name="assignedmultiowner">
							<option value="1" {if $RECORD_MODEL->get('assignedmultiowner') == '1'}selected="true"{/if}>{vtranslate('LBL_ALL_USERS', $QUALIFIED_MODULE)}</option>
							<option value="2" {if $RECORD_MODEL->get('assignedmultiowner') == '2'}selected="true"{/if}>{vtranslate('LBL_USERS_WITH_SAME_OR_LOWER_LEVEL', $QUALIFIED_MODULE)}</option>
							<option value="3" {if $RECORD_MODEL->get('assignedmultiowner') == '3'}selected="true"{/if}>{vtranslate('LBL_USERS_WITH_LOWER_LEVEL', $QUALIFIED_MODULE)}</option>
							<option value="4" {if $RECORD_MODEL->get('assignedmultiowner') == '4'}selected="true"{/if}>{vtranslate('LBL_JUST_ME', $QUALIFIED_MODULE)}</option>
							<option value="5" {if $RECORD_MODEL->get('assignedmultiowner') == '5'}selected="true"{/if}>{vtranslate('LBL_FROM_PANEL', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
                </div>
				<br>
				<div class="row">
					<label class="col-md-4"><span class="redColor">*</span><strong>{vtranslate('LBL_PROFILE',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						{assign var="ROLE_PROFILES" value=$RECORD_MODEL->getProfiles()}
						<select class="select2" multiple="true" id="profilesList" name="profiles[]" data-placeholder="{vtranslate('LBL_CHOOSE_PROFILES',$QUALIFIED_MODULE)}" data-validation-engine="validate[required]" style="width: 800px">
							{foreach from=$ALL_PROFILES item=PROFILE}
								{if $PROFILE->isDirectlyRelated() eq false}
									<option value="{$PROFILE->getId()}" {if isset($ROLE_PROFILES[$PROFILE->getId()])}selected="true"{/if}>{vtranslate($PROFILE->getName(),'Settings::Profiles')}</option>
								{/if}
							{/foreach}
						</select>
					</div>
				</div>
				<br>
				<div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_POSSIBLE_CHANGE_OWNER_OF_RECORD',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						<div class="pull-left">
							<input type="checkbox" value="1" {if $RECORD_MODEL->get('changeowner')} checked="" {/if} name="change_owner" class="alignTop"/>
						</div>
					</div>
				</div>
				<hr/>
				<div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_PERMISSIONS_TO_LIST_RELATED_RECORDS',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						<select id="listRelatedRecord" class="row select2 form-control" name="listRelatedRecord">
							<option value="0" {if $RECORD_MODEL->get('listrelatedrecord') == '0'}selected="true"{/if}>{vtranslate('LBL_INACTIVE', $QUALIFIED_MODULE)}</option>
							<option value="1" {if $RECORD_MODEL->get('listrelatedrecord') == '1'}selected="true"{/if}>{vtranslate('LBL_ONLY_PARENT', $QUALIFIED_MODULE)}</option>
							<option value="2" {if $RECORD_MODEL->get('listrelatedrecord') == '2'}selected="true"{/if}>{vtranslate('LBL_ACCORDING_TO_HIERARCHY', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
				<br>
				<div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_PERMISSIONS_TO_VIEW_RELATED_RECORDS',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						<select id="previewRelatedRecord" class="row select2 form-control" name="previewRelatedRecord">
							<option value="0" {if $RECORD_MODEL->get('previewrelatedrecord') == '0'}selected="true"{/if}>{vtranslate('LBL_INACTIVE', $QUALIFIED_MODULE)}</option>
							<option value="1" {if $RECORD_MODEL->get('previewrelatedrecord') == '1'}selected="true"{/if}>{vtranslate('LBL_ONLY_PARENT', $QUALIFIED_MODULE)}</option>
							<option value="2" {if $RECORD_MODEL->get('previewrelatedrecord') == '2'}selected="true"{/if}>{vtranslate('LBL_ACCORDING_TO_HIERARCHY', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
				<br>
				<div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_PERMISSIONS_FIELD_RELATED_RECORDS',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						{assign var="PERMISSIONS_RELATED_FIELD" value=explode(',',$RECORD_MODEL->get('permissionsrelatedfield'))}
						<select id="previewRelatedRecord" class="row select2 form-control" name="permissionsRelatedField[]" multiple >
							<option value="0" {if in_array('0', $PERMISSIONS_RELATED_FIELD)}selected="true"{/if}>{vtranslate('Assigned To', $QUALIFIED_MODULE)}</option>
							<option value="1" {if in_array('1', $PERMISSIONS_RELATED_FIELD)}selected="true"{/if}>{vtranslate('Share with users', $QUALIFIED_MODULE)}</option>
							<option value="2" {if in_array('2', $PERMISSIONS_RELATED_FIELD)}selected="true"{/if}>{vtranslate('LBL_PERMITTED_BY_SHARING', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
				<br>
				<div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_PERMISSIONS_TO_EDIT_RELATED_RECORDS',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						<div class="pull-left">
							<input type="checkbox" value="1" {if $RECORD_MODEL->get('editrelatedrecord')} checked="" {/if} name="editRelatedRecord" class="alignTop"/>
						</div>
					</div>
				</div>
				<hr/>
				<div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_SEARCH_WITHOUT_PERMISSION',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						{assign var="SEARCH_MODULES" value=explode(',',$RECORD_MODEL->get('searchunpriv'))}
						<select id="modulesList" class="row modules select2 form-control" multiple="true" name="searchunpriv[]">
							{foreach from=Vtiger_Module_Model::getAll([0],[],true) key=TABID item=MODULE_MODEL}
								<option value="{$MODULE_MODEL->getName()}" {if in_array($MODULE_MODEL->getName(), $SEARCH_MODULES)}selected="true"{/if}>{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<br>
				<div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_SHOW_GLOBAL_SEARCH_ADVANCED',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						<div class="pull-left">
							<input type="checkbox" value="1" {if $RECORD_MODEL->get('globalsearchadv')} checked="" {/if} name="globalSearchAdvanced" class="alignTop"/>
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<label class="col-md-4"><strong>{vtranslate('LBL_BROWSING_OTHER_USERS_GRAPHICAL_CALENDAR',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						<select id="clendarallorecords" class="row select2 form-control" name="clendarallorecords">
							<option value="1" {if $RECORD_MODEL->get('clendarallorecords') == '1'}selected="true"{/if}>{vtranslate('LBL_CLENDAR_ALLO_RECORDS_1', $QUALIFIED_MODULE)}</option>
							<option value="2" {if $RECORD_MODEL->get('clendarallorecords') == '2'}selected="true"{/if}>{vtranslate('LBL_CLENDAR_ALLO_RECORDS_2', $QUALIFIED_MODULE)}</option>
							<option value="3" {if $RECORD_MODEL->get('clendarallorecords') == '3'}selected="true"{/if}>{vtranslate('LBL_CLENDAR_ALLO_RECORDS_3', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
				<br>
				<hr>
				<div class="form-group paddingTop10">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_AUTO_ASSIGN_RECORDS',$QUALIFIED_MODULE)}:</strong></label>
					<div class="col-md-7 fieldValue">
						<div class="pull-left">
							<input type="checkbox" value="1" {if $RECORD_MODEL->get('auto_assign')} checked="" {/if} name="auto_assign" class="alignTop"/>
						</div>
					</div>
				</div>
			</div>
			<div class="pull-right marginRight10px paddingTop20">
				<button class="btn btn-success" type="submit">{vtranslate('LBL_SAVE',$MODULE)}</button>
				<button class="cancelLink btn btn-warning" onclick="javascript:window.history.back();" type="reset">{vtranslate('LBL_CANCEL',$MODULE)}</button>
			</div>
			{if count($ROLE_USERS) > 0 }
				<hr/>
				<h4>{vtranslate('LBL_USERS_LIST',$QUALIFIED_MODULE)}</h4>
				<br/>
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
