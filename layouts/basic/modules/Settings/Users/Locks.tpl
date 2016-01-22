{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<input type="hidden" id="lcount" value="{count($LOCKS)}" />
	{assign var="USERS" value=Users_Record_Model::getAll()}
	{assign var="ROLES" value=Settings_Roles_Record_Model::getAll()}
	<div class="widget_header row">
		<div class="col-md-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<span>{vtranslate('LBL_LOCKS_DESCRIPTION', $QUALIFIED_MODULE)}</span>
	<hr>
	<div>
		<div class="contents">
			<table class="locksTable table table-bordered">
				<thead>
					<tr class="listViewHeaders">
						<th class="col-md-3">{vtranslate('LBL_USER', $QUALIFIED_MODULE)}</th>
						<th class="col-md-8">{vtranslate('LBL_LOCKS', $QUALIFIED_MODULE)}</th>
						<th class="col-md-1">{vtranslate('LBL_TOOLS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach item=LOCK key=ID from=$LOCKS}
						{include file='LocksItem.tpl'|@vtemplate_path:$QUALIFIED_MODULE SELECT=true}
					{/foreach}
				</tbody>
			</table>
		</div>
		<br>
		<div>
			<button class="btn btn-info addItem"><strong>{vtranslate('LBL_ADD', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
			<button class="btn btn-success saveItems"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
		</div>
		<br>
		<table class="table table-bordered cloneItem hide">
			{assign var="LOCK" value=[]}
			{include file='LocksItem.tpl'|@vtemplate_path:$QUALIFIED_MODULE SELECT=false}
		</table>
	</div>
{/strip}

