{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="lcount" value="{count($LOCKS)}" />
	{assign var="USERS" value=Users_Record_Model::getAll()}
	{assign var="ROLES" value=Settings_Roles_Record_Model::getAll()}
	<div class="widget_header row">
		<div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
		</div>
	</div>
	<span>{\App\Language::translate('LBL_LOCKS_DESCRIPTION', $QUALIFIED_MODULE)}</span>
	<hr>
	<div>
		<div class="contents">
			<table class="locksTable table table-bordered">
				<thead>
					<tr class="listViewHeaders">
						<th class="col-md-3">{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}</th>
						<th class="col-md-8">{\App\Language::translate('LBL_LOCKS', $QUALIFIED_MODULE)}</th>
						<th class="col-md-1">{\App\Language::translate('LBL_TOOLS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach item=LOCK key=ID from=$LOCKS}
						{include file=\App\Layout::getTemplatePath('LocksItem.tpl', $QUALIFIED_MODULE) SELECT=true}
					{/foreach}
				</tbody>
			</table>
		</div>
		<br />
		<div>
			<button class="btn btn-info addItem"><strong>{\App\Language::translate('LBL_ADD', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
			<button class="btn btn-success saveItems"><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
		</div>
		<br />
		<table class="table table-bordered cloneItem d-none">
			{assign var="LOCK" value=[]}
			{include file=\App\Layout::getTemplatePath('LocksItem.tpl', $QUALIFIED_MODULE) SELECT=false}
		</table>
	</div>
{/strip}

