{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce Sp. z o.o
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Settings-Profiles-EditViewContents -->
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<div class="row padding1per">
		<span class="col-md-3"><h4>{\App\Language::translate('LBL_COPY_PRIVILEGES_FROM',"Settings:Roles")}</h4></span>
		<span class="col-md-6">
			<select class="select2" id="directProfilePriviligesSelect" style="min-width : 200px" data-placeholder="{\App\Language::translate('LBL_CHOOSE_PROFILES',$QUALIFIED_MODULE)}">
				<option></option>
				{foreach from=$ALL_PROFILES item=PROFILE}
					{if $PROFILE->isDirectlyRelated() eq false}
						<option value="{$PROFILE->getId()}" {if $RECORD_ID eq $PROFILE->getId()} selected="" {/if} >{$PROFILE->getName()}</option>
					{/if}
				{/foreach}
			</select>
		</span>
	</div>
	<br/>
	<div class="summaryWidgetContainer">
		<div class='row' style="padding-left: 15px;">
			<div class='col-12'>
				<label class="checkbox">
					<div class='col-sm-4 col-md-4'>
						<input type="hidden" name="viewall" value="0"/>
						<input type="checkbox" name="viewall" {if $RECORD_MODEL->hasGlobalReadPermission()}checked="true"{/if} />
						{\App\Language::translate('LBL_VIEW_ALL',$QUALIFIED_MODULE)}
					</div>
					<div class='col-sm-8 col-md-8'>
						<span style="margin-left:0px">
							<i class="fas fa-info-circle"></i>
							<span style="margin-left:2px">{\App\Language::translate('LBL_VIEW_ALL_DESC',$QUALIFIED_MODULE)}</span>
						</span>
					</div>
				</label>
			</div>
			<div class='col-12'>
				<label class="checkbox">
					<div class='col-sm-4 col-md-4'>
						<input type="hidden" name="editall" value="0"/>
						<input type="checkbox" name="editall" {if $RECORD_MODEL->hasGlobalWritePermission()}checked="true"{/if} />
						{\App\Language::translate('LBL_EDIT_ALL',$QUALIFIED_MODULE)}
					</div>
					<div class='col-sm-8 col-md-8'>
						<span style="margin-left:0px">
							<i class="fas fa-info-circle"></i>
							<span style="margin-left:2px">{\App\Language::translate('LBL_EDIT_ALL_DESC',$QUALIFIED_MODULE)}</span>
						</span>
					</div>
				</label>
			</div>
		</div>
	</div>
	<table class="table customTableRWD table-bordered profilesEditView">
		<thead>
		<tr class="blockHeader">
			<th width="30%" style="border-left: 1px solid #DDD !important;">
				<input checked="true" class="alignTop" type="checkbox" id="mainModulesCheckBox"/>&nbsp;
				{\App\Language::translate('LBL_MODULES', $QUALIFIED_MODULE)}
			</th>
			<th data-hide='phone' width="14%" style="border-left: 1px solid #DDD !important;">
				<input type="checkbox" {if !$RECORD_ID} class="alignTop" checked="true" {/if} id="mainAction4CheckBox"/>&nbsp;
				{\App\Language::translate('LBL_VIEW_PRIVILEGE', $QUALIFIED_MODULE)}
			</th>
			<th data-hide='phone' width="14%" style="border-left: 1px solid #DDD !important;">
				<input {if !$RECORD_ID} class="alignTop"  checked="true" {/if} type="checkbox" id="mainAction1CheckBox"/>&nbsp;
				{\App\Language::translate('LBL_EDIT_PRIVILIGE', $QUALIFIED_MODULE)}
			</th>
			<th data-hide='phone' width="14%" style="border-left: 1px solid #DDD !important;">
				<input checked="true" class="alignTop" type="checkbox" id="mainAction2CheckBox"/>&nbsp;
				{\App\Language::translate('LBL_DELETE_PRIVILIGE', $QUALIFIED_MODULE)}
			</th>
			<th width="28%" style="border-left: 1px solid #DDD !important;" nowrap="nowrap">{\App\Language::translate('LBL_FIELD_AND_TOOL_PRVILIGES', $QUALIFIED_MODULE)}</th>
		</tr>
		</thead>
		<tbody>
		{assign var=PROFILE_MODULES value=$RECORD_MODEL->getModulePermissions()}
		{foreach from=$PROFILE_MODULES key=TABID item=PROFILE_MODULE}
			{assign var=MODULE_NAME value=$PROFILE_MODULE->getName()}
			<tr>
				<td>
					<input class="modulesCheckBox alignTop" type="checkbox" name="permissions[{$TABID}][is_permitted]" data-value="{$TABID}" data-module-state="" {if $RECORD_MODEL->hasModulePermission($PROFILE_MODULE)}checked="true"{else} data-module-unchecked="true" {/if}> {\App\Language::translate($PROFILE_MODULE->get('label'), $PROFILE_MODULE->getName())}
				</td>
				{assign var="BASIC_ACTION_ORDER" value=array(2,0,1)}
				{foreach from=$BASIC_ACTION_ORDER item=ORDERID}
					<td style="border-left: 1px solid #DDD !important;">
					{assign var="ACTION_MODEL" value=$ALL_BASIC_ACTIONS[$ORDERID]}
					{assign var=ACTION_ID value=$ACTION_MODEL->get('actionid')}
					{if $ACTION_MODEL->isModuleEnabled($PROFILE_MODULE)}
						<input style="margin-left: 45% !important" class="action{$ACTION_ID}CheckBox" type="checkbox" name="permissions[{$TABID}][actions][{$ACTION_ID}]" data-action-state="{$ACTION_MODEL->getName()}" {if empty($RECORD_ID)} checked="true" {/if} {if $RECORD_ID} {if $RECORD_MODEL->hasModuleActionPermission($PROFILE_MODULE, $ACTION_MODEL)}checked="true"{else} data-action{$ACTION_ID}-unchecked="true"{/if} {/if}>
						</td>
					{/if}
					</td>
				{/foreach}
				<td style="border-left: 1px solid #DDD !important;">
					{if $PROFILE_MODULE->getFields()}
						<div class="row">
							<span class="col-md-4">&nbsp;</span>
							<span class="col-md-4"><button type="button" data-handlerfor="fields" data-togglehandler="{$TABID}-fields" class="btn btn-sm btn-light" style="padding-right: 20px; padding-left: 20px;">
											<i class="fas fa-chevron-down"></i>
										</button></span>
						</div>
					{/if}
				</td>
			</tr>
			<tr class="d-none">
				<td colspan="6" data-toggle-visible="false" class="row" style="padding-left: 5%;padding-right: 5%">
					<div class="row" data-togglecontent="{$TABID}-fields">
						{if $PROFILE_MODULE->getFields()}
						<div class="col-md-12">
							<label class="themeTextColor font-x-large float-left"><strong>{\App\Language::translate('LBL_FIELDS',$QUALIFIED_MODULE)}</strong></label>
							<div class="float-right">
											<span class="mini-slider-control ui-slider" data-value="0">
												<a style="margin-top: 3px" class="ui-slider-handle"></a>
											</span>
								<span style="margin: 0 20px;">{\App\Language::translate('LBL_INVISIBLE',$QUALIFIED_MODULE)}</span>&nbsp;&nbsp;
								<span class="mini-slider-control ui-slider" data-value="1">
												<a style="margin-top: 3px" class="ui-slider-handle"></a>
											</span>
								<span style="margin: 0 20px;">{\App\Language::translate('LBL_READ_ONLY',$QUALIFIED_MODULE)}</span>&nbsp;&nbsp;
								<span class="mini-slider-control ui-slider" data-value="2">
												<a style="margin-top: 3px" class="ui-slider-handle"></a>
											</span>
								<span style="margin: 0 20px;">{\App\Language::translate('LBL_WRITE',$QUALIFIED_MODULE)}</span>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="col-md-12 paddingLRZero row ">
							{assign var=COUNTER value=0}
							{foreach from=$PROFILE_MODULE->getFields() key=FIELD_NAME item=FIELD_MODEL name="fields"}
								{if $FIELD_MODEL->isActiveField()}
									{assign var="FIELD_ID" value=$FIELD_MODEL->getId()}
									{if $COUNTER % 3 == 0}
										<div class='col-md-12'>
									{/if}
									<div class='col-md-4 col-sm-6 col-12 padding10 div-bordered' style="border-left: 1px solid #DDD !important;">
										{assign var="FIELD_LOCKED" value=$RECORD_MODEL->isModuleFieldLocked($PROFILE_MODULE, $FIELD_MODEL)}
										<input type="hidden" name="permissions[{$TABID}][fields][{$FIELD_ID}]" data-range-input="{$FIELD_ID}" value="{$RECORD_MODEL->getModuleFieldPermissionValue($PROFILE_MODULE, $FIELD_MODEL)}" readonly="true">
										<div class="mini-slider-control editViewMiniSlider float-left" data-locked="{$FIELD_LOCKED}" data-range="{$FIELD_ID}" data-value="{$RECORD_MODEL->getModuleFieldPermissionValue($PROFILE_MODULE, $FIELD_MODEL)}"></div>
										<div class="float-left">
											{if $FIELD_MODEL->isMandatory()}
												<span class="redColor">*</span>
											{/if} {\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
										</div>
									</div>
									{if $smarty.foreach.fields.last OR ($COUNTER+1) % 3 == 0}
										</div>
									{/if}
									{assign var=COUNTER value=$COUNTER+1}
								{/if}
							{/foreach}
						</div>
					</div>
					</ul>
					{/if}
					</div>
				</td>
			</tr>
			<tr class="d-none">
				<td colspan="6" data-toggle-visible="false" class="row" style="padding-left: 5%;padding-right: 5%;background-image: none !important;">
					<div class="row" data-togglecontent="{$TABID}-fields">
						{assign var=UTILITY_ACTION_COUNT value=0}
						{assign var="ALL_UTILITY_ACTIONS_ARRAY" value=[]}
						{foreach from=$ALL_UTILITY_ACTIONS item=ACTION_MODEL}
							{if $ACTION_MODEL->isModuleEnabled($PROFILE_MODULE)}
								{append var="ALL_UTILITY_ACTIONS_ARRAY" value=$ACTION_MODEL}
							{/if}
						{/foreach}
						{if $ALL_UTILITY_ACTIONS_ARRAY}
							<div class="col-12">
								<label class="themeTextColor font-x-large float-left"><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></label>
							</div>
							<div class="col-md-12 paddingLRZero marginBottom10px">
								{foreach from=$ALL_UTILITY_ACTIONS_ARRAY item=ACTION_MODEL name="actions"}
									{if $smarty.foreach.actions.index % 3 == 0}
										<div class='paddingLRZero col-md-12'>
									{/if}
									{assign var=ACTIONID value=$ACTION_MODEL->get('actionid')}
									<div class='col-md-4 col-sm-6 col-12 padding10' {if $smarty.foreach.actions.last && (($smarty.foreach.actions.index+1) % 3 neq 0)}
											{assign var="index" value=($smarty.foreach.actions.index+1) % 3}
											{assign var="colspan" value=4-$index}
										colspan="{$colspan}"
											{/if}>
										<input type="checkbox" class="alignTop" name="permissions[{$TABID}][actions][{$ACTIONID}]" {if $RECORD_MODEL->hasModuleActionPermission($PROFILE_MODULE, $ACTIONID)}checked="true" {elseif empty($RECORD_ID) && empty($IS_DUPLICATE_RECORD)} checked="true" {/if}> {\App\Language::translate($ACTION_MODEL->getName(),$QUALIFIED_MODULE)}
									</div>
									{if $smarty.foreach.actions.last OR ($smarty.foreach.actions.index+1) % 3 == 0}
										</div>
									{/if}
								{/foreach}
							</div>
						{/if}
					</div>
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<!-- /tpl-Settings-Profiles-EditViewContents -->
{/strip}
