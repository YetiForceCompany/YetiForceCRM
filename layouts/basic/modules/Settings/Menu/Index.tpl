{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}
<div class="menuConfigContainer">
	<div class="widget_header row">
		<div class="col-md-7">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate('LBL_MENU_BUILDER_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
		<div class="col-md-5 row">
			<div class="col-xs-6 paddingLRZero">
				<button class="btn btn-default addMenu pull-right"><strong>{vtranslate('LBL_ADD_MENU', $QUALIFIED_MODULE)}</strong></button>
			</div>
			<div class="col-xs-6 pull-right ">
				<select class="select2 form-control" name="roleMenu">
					<option value="0" {if $ROLEID eq 0} selected="" {/if}>{vtranslate('LBL_DEFAULT_MENU', $QUALIFIED_MODULE)}</option>
					{foreach item=ROLE key=KEY from=Settings_Roles_Record_Model::getAll()}
						<option value="{$KEY}" {if $ROLEID === $KEY} selected="" {/if}>{vtranslate($ROLE->getName())}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>
	<hr>
	{if !$DATA}
		<button class="btn btn-success copyMenu"><strong>{vtranslate('LBL_COPY_MENU', $QUALIFIED_MODULE)}</strong></button>
	{/if}
	<div class="treeMenuContainer">
		<input type="hidden" id="treeLastID" value="{$LASTID}" />
		<input type="hidden" name="tree" id="treeValues" value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($DATA))}' />
		<div id="treeContent"></div>
	</div>
	<div class="modal fade copyMenuModal">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<form>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">{vtranslate('LBL_COPY_MENU', $QUALIFIED_MODULE)}</h4>
					</div>
					<div class="modal-body">
						<select id="roleList" class="form-control" name="roles" data-validation-engine="validate[required]">
							<option value="0">{vtranslate('LBL_DEFAULT_MENU', $QUALIFIED_MODULE)}</option>
							{foreach item=ROLE key=KEY from=$ROLES_CONTAIN_MENU}
								<option value="{$ROLE['roleId']}"  >{vtranslate($ROLE['roleName'])}</option>
							{/foreach}
						</select>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
						<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{vtranslate('LBL_CLOSE', $QUALIFIED_MODULE)}</button>
					</div>
				</form>
			</div>
		</div>
	</div>	
</div>
<div class="modal deleteAlert fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title">{vtranslate('LBL_REMOVE_TITLE', $QUALIFIED_MODULE)}</h3>
			</div>
			<div class="modal-body">
				<p>{vtranslate('LBL_REMOVE_DESC', $QUALIFIED_MODULE)}</p>
			</div>
			<div class="modal-footer">
				<div class="pull-right">
					<button class="btn btn-warning cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
				</div>
				<div class="pull-right">
					<button class="btn btn-danger" data-dismiss="modal">{vtranslate('LBL_REMOVE', $QUALIFIED_MODULE)}</button>
				</div>
			</div>
		</div>
	</div>
</div>
