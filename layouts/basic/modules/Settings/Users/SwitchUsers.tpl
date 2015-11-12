{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<input type="hidden" id="suCount" value="{count($SWITCH_USERS)}" />
	{assign var="USERS" value=Users_Record_Model::getAll()}
	{assign var="ROLES" value=Settings_Roles_Record_Model::getAll()}
	<div class="widget_header row">
		<div class="col-md-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<span style="font-size:12px;color: black;">{vtranslate('LBL_SWITCH_USERS_DESCRIPTION', $QUALIFIED_MODULE)}</span>
	<hr>
	<div class="container-fluid ">
		<div class="contents">
			<table class="switchUsersTable table table-bordered">
				<thead>
					<tr class="listViewHeaders">
						<th class="col-md-3">{vtranslate('LBL_SU_BASE_ACCESS', $QUALIFIED_MODULE)}</th>
						<th class="col-md-8">{vtranslate('LBL_SU_AVAILABLE_ACCESS', $QUALIFIED_MODULE)}</th>
						<th class="col-md-1">{vtranslate('LBL_TOOLS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach item=SUSERS key=ID from=$MODULE_MODEL->getSwitchUsers()}
						{include file='SwitchUsersItem.tpl'|@vtemplate_path:$QUALIFIED_MODULE SELECT=true}
					{/foreach}
				</tbody>
			</table>
		</div>
		<br>
		<div class="row">
			<button class="btn btn-info addItem"><strong>{vtranslate('LBL_ADD', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
			<button class="btn btn-success saveItems"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
		</div>
		<br>
		<table class="table table-bordered cloneItem hide">
			{assign var="SUSERS" value=[]}
			{include file='SwitchUsersItem.tpl'|@vtemplate_path:$QUALIFIED_MODULE SELECT=false}
		</table>
	</div>
{/strip}

