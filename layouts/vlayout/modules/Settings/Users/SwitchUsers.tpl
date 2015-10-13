{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<input type="hidden" id="suCount" value="{count($SWITCH_USERS)}" />
	{assign var="USERS" value=Users_Record_Model::getAll()}
	{assign var="ROLES" value=Settings_Roles_Record_Model::getAll()}
	<div class="container-fluid">
		<div class="row widget_header">
			<div class="">
				<h3>{vtranslate('LBL_SWITCH_USERS', $QUALIFIED_MODULE)}</h3>
				<span style="font-size:12px;color: black;">{vtranslate('LBL_SWITCH_USERS_DESCRIPTION', $QUALIFIED_MODULE)}</span>
			</div>
			<hr>
		</div>
		<div class="row contents">
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
		<div class="row">
			<br/>
			<button class="btn btn-info addItem"><strong>{vtranslate('LBL_ADD', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
			<button class="btn btn-success saveItems"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
		</div>
	</div>
	<table class="row cloneItem hide">
		{assign var="SUSERS" value=[]}
		{include file='SwitchUsersItem.tpl'|@vtemplate_path:$QUALIFIED_MODULE SELECT=false}
	</table>
{/strip}

