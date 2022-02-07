{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="row widget_header tpl-Settings-AdvancedPermission-EditVewS1">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="editViewContainer">
		<form name="EditAdvPermission" action="index.php" method="post" id="EditView" class="form-horizontal">
			<input type="hidden" name="module" value="AdvancedPermission">
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" name="mode" value="step1" />
			<div class="form-group row mt-2">
				<label class="col-sm-2 col-form-label text-right">
					<span class="redColor">*</span> {\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-10">
					<input class="form-control" name="name" value="{$RECORD_MODEL->getName()}" data-validation-engine="validate[required]">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 col-form-label text-right">
					<span class="redColor">*</span> {\App\Language::translate('LBL_ACTION', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-10">
					<select class="row modules select2 form-control" name="actions" data-validation-engine="validate[required]">
						{foreach from=Settings_AdvancedPermission_Module_Model::$action key=ID item=NAME}
							<option value="{$ID}" {if $RECORD_MODEL->get('action') == $ID}selected="true" {/if}>{\App\Language::translate($NAME, $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 col-form-label text-right">
					<span class="redColor">*</span> {\App\Language::translate('LBL_STATUS', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-10">
					<select class="row modules select2 form-control" name="status" data-validation-engine="validate[required]">
						{foreach from=Settings_AdvancedPermission_Module_Model::$status key=ID item=NAME}
							<option value="{$ID}" {if $RECORD_MODEL->get('status') == $ID}selected="true" {/if}>{\App\Language::translate($NAME, $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 col-form-label text-right">
					<span class="redColor">*</span> {\App\Language::translate('LBL_PRIORITY', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-10">
					<select class="row modules select2 form-control" name="priority" data-validation-engine="validate[required]">
						{foreach from=Settings_AdvancedPermission_Module_Model::$priority key=ID item=NAME}
							<option value="{$ID}" {if $RECORD_MODEL->get('priority') == $ID}selected="true" {/if}>{\App\Language::translate($NAME)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 col-form-label text-right">
					<span class="redColor">*</span> {\App\Language::translate('LBL_MODULE', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-10">
					<select class="row modules select2 form-control" name="tabid" data-validation-engine="validate[required]">
						{foreach from=Vtiger_Module_Model::getAll([0],[],true) key=TABID item=MODULE_MODEL}
							<option value="{$TABID}" {if $RECORD_MODEL->get('tabid') == $TABID}selected="true" {/if}>{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-sm-2"></div>
				<div class="col-sm-10 groupMembersColors">
					<ul class="nav nav-pills">
						<li class="Users padding5per textAlignCenter"><strong>{\App\Language::translate('LBL_USERS', $QUALIFIED_MODULE)}</strong></li>
						<li class="Groups padding5per textAlignCenter"><strong>{\App\Language::translate('LBL_GROUPS', $QUALIFIED_MODULE)}</strong></li>
						<li class="Roles padding5per textAlignCenter"><strong>{\App\Language::translate('LBL_ROLES', $QUALIFIED_MODULE)}</strong></li>
						<li class="RoleAndSubordinates padding5per textAlignCenter"><strong>{\App\Language::translate('RoleAndSubordinates', $QUALIFIED_MODULE)}</strong></li>
					</ul>
				</div>
				<label class="col-sm-2 col-form-label text-right">
					<span class="redColor">*</span> {\App\Language::translate('LBL_MEMBERS', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-10">
					<select class="row modules select2 form-control groupMembersColors" multiple="true" name="members[]" data-validation-engine="validate[required]">
						{foreach from=\App\PrivilegeUtil::getMembers() key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
							<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
								{foreach from=$ALL_GROUP_MEMBERS key=MEMBER_ID item=MEMBER}
									<option class="{$MEMBER['type']}" value="{$MEMBER_ID}" {if $RECORD_MODEL->get('members') && in_array($MEMBER_ID, $RECORD_MODEL->get('members'))}selected="true" {/if}>{\App\Language::translate($MEMBER['name'])}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-md-5 float-right">
					<span class="float-right">
						<button class="btn btn-success" type="submit"><strong><span class="fa fa-caret-right u-mr-5px"></span>{\App\Language::translate('LBL_SAVE_AND_CONDITIONS', $QUALIFIED_MODULE)}</strong></button>
						<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();"><span class="fa fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
					</span>
				</div>
			</div>
		</form>
	</div>
{/strip}
