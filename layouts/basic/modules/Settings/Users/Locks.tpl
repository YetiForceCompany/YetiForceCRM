{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="js-lock-count" data-js="value" value="{count($LOCKS)}" />
	{assign var="USERS" value=Users_Record_Model::getAll()}
	{assign var="ROLES" value=Settings_Roles_Record_Model::getAll()}
	<div class="o-breadcrumb widget_header row">
		<div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<span>{\App\Language::translate('LBL_LOCKS_DESCRIPTION', $QUALIFIED_MODULE)}</span>
	<table class="js-locks-table table table-bordered" data-js="data">
		<thead>
			<tr class="listViewHeaders">
				<th class="w-25">{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}</th>
				<th>{\App\Language::translate('LBL_LOCKS', $QUALIFIED_MODULE)}</th>
				<th>{\App\Language::translate('LBL_TOOLS', $QUALIFIED_MODULE)}</th>
			</tr>
		</thead>
		<tbody>
			{foreach item=LOCK key=ID from=$LOCKS}
				{include file=\App\Layout::getTemplatePath('LocksItem.tpl', $QUALIFIED_MODULE) SELECT=true}
			{/foreach}
		</tbody>
	</table>
	<div class="mt-3">
		<button class="btn btn-info js-add-item mr-2" data-js="click"><span class="fas fa-plus mr-1"></span><strong>{\App\Language::translate('LBL_ADD', $QUALIFIED_MODULE)}</strong></button>
		<button class="btn btn-success js-save-items" data-js="click"><span class="fas fa-check mr-1"></span><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
	</div>
	<table class="table table-bordered js-clone-item d-none" data-js="clone">
		{assign var="LOCK" value=[]}
		{include file=\App\Layout::getTemplatePath('LocksItem.tpl', $QUALIFIED_MODULE) SELECT=false}
	</table>
{/strip}
