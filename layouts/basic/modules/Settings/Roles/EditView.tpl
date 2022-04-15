{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<div class="tpl-Settings-Roles-EditView">
		<div class="row widget_header">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<form name="EditRole" action="index.php" method="post" id="EditView" class="form-horizontal">
			<input type="hidden" name="module" value="Roles"/>
			<input type="hidden" name="action" value="Save"/>
			<input type="hidden" name="parent" value="Settings"/>
			{assign var=RECORD_ID value=$RECORD_MODEL->getId()}
			<input type="hidden" name="record" value="{$RECORD_ID}"/>
			{assign var=HAS_PARENT value="{if $RECORD_MODEL->getParent()}true{/if}"}
			{if $HAS_PARENT}
				<input type="hidden" name="parent_roleid" value="{$RECORD_MODEL->getParent()->getId()}">
			{/if}
			<div class="mt-2">
				<div class="row mb-2">
					<div class="col-md-4">
						<label class=""><span
									class="redColor">*</span><strong>{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}
								: </strong></label>
					</div>
					<div class=" col-md-7 ">
						<input type="text" class="fieldValue form-control" name="rolename" id="profilename"
							   value="{$RECORD_MODEL->getName()}" data-validation-engine="validate[required]"/>
					</div>
				</div>
				<div class="row mb-2">
					<label class="col-md-4">
						<strong>{\App\Language::translate('LBL_REPORTS_TO', $QUALIFIED_MODULE)}: </strong>
					</label>
					<div class="col-md-7 fieldValue">
						<input type="hidden" name="parent_roleid"
							   {if $HAS_PARENT}value="{$RECORD_MODEL->getParent()->getId()}"{/if}>
						<input type="text" class="form-control" name="parent_roleid_display"
							   {if $HAS_PARENT}value="{\App\Language::translate($RECORD_MODEL->getParent()->getName(), $QUALIFIED_MODULE)}"{/if}
							   readonly>
					</div>
				</div>
				<div class="row mb-2">
					<label class="col-md-4">
						<strong>{\App\Language::translate('LBL_MULTI_COMPANY', $QUALIFIED_MODULE)}: </strong>
					</label>
					<div class="col-md-7 fieldValue">
						<select id="company" class="row select2 form-control" name="company">
							{foreach from=$RECORD_MODEL->getMultiCompany() item=COMPANY}
								<option value="{$COMPANY['multicompanyid']}"
										{if $RECORD_MODEL->get('company') == $COMPANY['multicompanyid']}selected="true"{/if}>
									{$COMPANY['company_name']}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="row mb-2">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_CAN_ASSIGN_OWNER_TO', $QUALIFIED_MODULE)}
							: </strong></label>
					<div class="col-md-7 fieldValue">
						<select id="allowassignedrecordsto" class="row select2 form-control"
								name="allowassignedrecordsto">
							<option value="1"
									{if $RECORD_MODEL->get('allowassignedrecordsto') == '1'}selected="true"{/if}>{\App\Language::translate('LBL_ALL_USERS', $QUALIFIED_MODULE)}</option>
							<option value="2"
									{if $RECORD_MODEL->get('allowassignedrecordsto') == '2'}selected="true"{/if}>{\App\Language::translate('LBL_USERS_WITH_SAME_OR_LOWER_LEVEL', $QUALIFIED_MODULE)}</option>
							<option value="3"
									{if $RECORD_MODEL->get('allowassignedrecordsto') == '3'}selected="true"{/if}>{\App\Language::translate('LBL_USERS_WITH_LOWER_LEVEL', $QUALIFIED_MODULE)}</option>
							<option value="4"
									{if $RECORD_MODEL->get('allowassignedrecordsto') == '4'}selected="true"{/if}>{\App\Language::translate('LBL_JUST_ME', $QUALIFIED_MODULE)}</option>
							<option value="5"
									{if $RECORD_MODEL->get('allowassignedrecordsto') == '5'}selected="true"{/if}>{\App\Language::translate('LBL_FROM_PANEL', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
				<div class="row mb-2">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_CAN_ASSIGN_MULTIOWNER_TO', $QUALIFIED_MODULE)}
							: </strong></label>
					<div class="col-md-7 fieldValue">
						<select id="assignedmultiowner" class="row select2 form-control" name="assignedmultiowner">
							<option value="1"
									{if $RECORD_MODEL->get('assignedmultiowner') == '1'}selected="true"{/if}>{\App\Language::translate('LBL_ALL_USERS', $QUALIFIED_MODULE)}</option>
							<option value="2"
									{if $RECORD_MODEL->get('assignedmultiowner') == '2'}selected="true"{/if}>{\App\Language::translate('LBL_USERS_WITH_SAME_OR_LOWER_LEVEL', $QUALIFIED_MODULE)}</option>
							<option value="3"
									{if $RECORD_MODEL->get('assignedmultiowner') == '3'}selected="true"{/if}>{\App\Language::translate('LBL_USERS_WITH_LOWER_LEVEL', $QUALIFIED_MODULE)}</option>
							<option value="4"
									{if $RECORD_MODEL->get('assignedmultiowner') == '4'}selected="true"{/if}>{\App\Language::translate('LBL_JUST_ME', $QUALIFIED_MODULE)}</option>
							<option value="5"
									{if $RECORD_MODEL->get('assignedmultiowner') == '5'}selected="true"{/if}>{\App\Language::translate('LBL_FROM_PANEL', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
				<div class="row mb-2">
					<label class="col-md-4"><span
								class="redColor">*</span><strong>{\App\Language::translate('LBL_PROFILE',$QUALIFIED_MODULE)}
							:</strong></label>
					<div class="col-md-7 fieldValue">
						{assign var="ROLE_PROFILES" value=$RECORD_MODEL->getProfiles()}
						<select class="select2" multiple="true" id="profilesList" name="profiles[]"
								data-placeholder="{\App\Language::translate('LBL_CHOOSE_PROFILES',$QUALIFIED_MODULE)}"
								data-validation-engine="validate[required]" style="width: 800px">
							{foreach from=$ALL_PROFILES item=PROFILE}
								{if $PROFILE->isDirectlyRelated() eq false}
									{assign var="PROFILE_ID" value=$PROFILE->getId()}
									<option value="{$PROFILE_ID}"
											{if isset($ROLE_PROFILES[$PROFILE_ID])}selected="true"{/if}>{\App\Language::translate($PROFILE->getName(),'Settings::Profiles')}</option>
								{/if}
							{/foreach}
						</select>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_POSSIBLE_CHANGE_OWNER_OF_RECORD',$QUALIFIED_MODULE)}
							:</strong></label>
					<div class="col-md-7 fieldValue">
						<div class="float-left">
							<input type="checkbox" value="1" {if $RECORD_MODEL->get('changeowner')} checked="" {/if}
								   name="changeowner" class="alignTop"/>
						</div>
					</div>
				</div>
				<hr/>
				<div class="row mb-2">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_PERMISSIONS_TO_LIST_RELATED_RECORDS',$QUALIFIED_MODULE)}
							:</strong></label>
					<div class="col-md-7 fieldValue">
						<select id="listRelatedRecord" class="row select2 form-control" name="listRelatedRecord">
							<option value="0"
									{if $RECORD_MODEL->get('listrelatedrecord') == '0'}selected="true"{/if}>{\App\Language::translate('LBL_INACTIVE', $QUALIFIED_MODULE)}</option>
							<option value="1"
									{if $RECORD_MODEL->get('listrelatedrecord') == '1'}selected="true"{/if}>{\App\Language::translate('LBL_ONLY_PARENT', $QUALIFIED_MODULE)}</option>
							<option value="2"
									{if $RECORD_MODEL->get('listrelatedrecord') == '2'}selected="true"{/if}>{\App\Language::translate('LBL_ACCORDING_TO_HIERARCHY', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
				<div class="row mb-2">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_PERMISSIONS_TO_VIEW_RELATED_RECORDS',$QUALIFIED_MODULE)}
							:</strong></label>
					<div class="col-md-7 fieldValue">
						<select id="previewRelatedRecord" class="row select2 form-control" name="previewRelatedRecord">
							<option value="0"
									{if $RECORD_MODEL->get('previewrelatedrecord') == '0'}selected="true"{/if}>{\App\Language::translate('LBL_INACTIVE', $QUALIFIED_MODULE)}</option>
							<option value="1"
									{if $RECORD_MODEL->get('previewrelatedrecord') == '1'}selected="true"{/if}>{\App\Language::translate('LBL_ONLY_PARENT', $QUALIFIED_MODULE)}</option>
							<option value="2"
									{if $RECORD_MODEL->get('previewrelatedrecord') == '2'}selected="true"{/if}>{\App\Language::translate('LBL_ACCORDING_TO_HIERARCHY', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
				<div class="row mb-2">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_PERMISSIONS_FIELD_RELATED_RECORDS',$QUALIFIED_MODULE)}
							:</strong></label>
					<div class="col-md-7 fieldValue">
						{if !$RECORD_MODEL->get('permissionsrelatedfield')}
							{assign var="PERMISSIONS_RELATED_FIELD" value=[]}
						{else}
							{assign var="PERMISSIONS_RELATED_FIELD" value=explode(',',$RECORD_MODEL->get('permissionsrelatedfield'))}
						{/if}
						<select id="permissionsRelatedField" class="row select2 form-control"
								name="permissionsRelatedField[]" multiple>
							<option value="0"
									{if in_array('0', $PERMISSIONS_RELATED_FIELD)}selected="true"{/if}>{\App\Language::translate('Assigned To', $QUALIFIED_MODULE)}</option>
							<option value="1"
									{if in_array('1', $PERMISSIONS_RELATED_FIELD)}selected="true"{/if}>{\App\Language::translate('Share with users', $QUALIFIED_MODULE)}</option>
							<option value="2"
									{if in_array('2', $PERMISSIONS_RELATED_FIELD)}selected="true"{/if}>{\App\Language::translate('LBL_PERMITTED_BY_SHARING', $QUALIFIED_MODULE)}</option>
							<option value="3"
									{if in_array('3', $PERMISSIONS_RELATED_FIELD)}selected="true"{/if}>{\App\Language::translate('LBL_PERMITTED_BY_READ_ACCESS', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_PERMISSIONS_TO_EDIT_RELATED_RECORDS',$QUALIFIED_MODULE)}
							:</strong></label>
					<div class="col-md-7 fieldValue">
						<div class="float-left">
							<input type="checkbox"
								   value="1" {if $RECORD_MODEL->get('editrelatedrecord')} checked="" {/if}
								   name="editRelatedRecord" class="alignTop"/>
						</div>
					</div>
				</div>
				<hr/>
				<div class="row mb-2">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_SEARCH_WITHOUT_PERMISSION',$QUALIFIED_MODULE)}
							:</strong></label>
					<div class="col-md-7 fieldValue">
						{if !$RECORD_MODEL->get('searchunpriv')}
							{assign var="SEARCH_MODULES" value=[]}
						{else}
							{assign var="SEARCH_MODULES" value=explode(',',$RECORD_MODEL->get('searchunpriv'))}
						{/if}
						<select id="modulesList" class="row modules select2 form-control" multiple="true"
								name="searchunpriv[]">
							{foreach from=Vtiger_Module_Model::getAll([0],[],true) key=TABID item=MODULE_MODEL}
								<option value="{$MODULE_MODEL->getName()}"
										{if in_array($MODULE_MODEL->getName(), $SEARCH_MODULES)}selected="true"{/if}>{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="row mb-2">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_SHOW_GLOBAL_SEARCH_ADVANCED',$QUALIFIED_MODULE)}
							:</strong></label>
					<div class="col-md-7 fieldValue">
						<div class="float-left">
							<input type="checkbox" value="1" {if $RECORD_MODEL->get('globalsearchadv')} checked="" {/if}
								   name="globalSearchAdvanced" class="alignTop"/>
						</div>
					</div>
				</div>
				<div class="row mb-2">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_BROWSING_OTHER_USERS_GRAPHICAL_CALENDAR',$QUALIFIED_MODULE)}
							:</strong></label>
					<div class="col-md-7 fieldValue">
						<select id="clendarallorecords" class="row select2 form-control" name="clendarallorecords">
							<option value="1"
									{if $RECORD_MODEL->get('clendarallorecords') == '1'}selected="true"{/if}>{\App\Language::translate('LBL_CLENDAR_ALLO_RECORDS_1', $QUALIFIED_MODULE)}</option>
							<option value="2"
									{if $RECORD_MODEL->get('clendarallorecords') == '2'}selected="true"{/if}>{\App\Language::translate('LBL_CLENDAR_ALLO_RECORDS_2', $QUALIFIED_MODULE)}</option>
							<option value="3"
									{if $RECORD_MODEL->get('clendarallorecords') == '3'}selected="true"{/if}>{\App\Language::translate('LBL_CLENDAR_ALLO_RECORDS_3', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
				<hr>
				<div class="form-group row">
					<label class="col-md-4"><strong>{\App\Language::translate('LBL_AUTO_ASSIGN_RECORDS',$QUALIFIED_MODULE)}
							:</strong></label>
					<div class="col-md-7 fieldValue">
						<div class="float-left">
							<input type="checkbox" value="1" {if $RECORD_MODEL->get('auto_assign')} checked="" {/if}
								   name="auto_assign" class="alignTop"/>
						</div>
					</div>
				</div>
			</div>
			<div class="float-right marginRight10px paddingTop20">
				<button class="btn btn-success" type="submit"><span
							class="fa fa-check u-mr-5px"></span>{\App\Language::translate('LBL_SAVE',$MODULE)}</button>
				<button class="cancelLink btn btn-warning" onclick="javascript:window.history.back();" type="reset">
					<span class="fa fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL',$MODULE)}</button>
			</div>
			{if count($ROLE_USERS) > 0 }
				<hr/>
				<h4>{\App\Language::translate('LBL_USERS_LIST',$QUALIFIED_MODULE)}</h4>
				<br/>
				<table class="table table-striped">
					<thead>
					<tr>
						<th>{\App\Language::translate('User Name','Users')}</th>
						<th>{\App\Language::translate('First Name','Users')}</th>
						<th>{\App\Language::translate('Last Name','Users')}</th>
						<th>{\App\Language::translate('Email','Users')}</th>
						<th>{\App\Language::translate('Status','Users')}</th>
					</tr>
					</thead>
					<tbody>
					{foreach from=$ROLE_USERS key=key item=USER}
						<tr>
							<td>{$USER->get('user_name')}</td>
							<td>{$USER->get('first_name')}</td>
							<td>{$USER->get('last_name')}</td>
							<td>{$USER->get('email1')}</td>
							<td>{\App\Language::translate($USER->get('status'),'Users')}</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			{/if}
		</form>
	</div>
{/strip}
