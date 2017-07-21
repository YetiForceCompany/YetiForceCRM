{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="widget_header row">
		<div class="col-md-6">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{\App\Language::translate('LBL_WEBSERVICE_APPS_DESCRIPTION',$QUALIFIED_MODULE)}
			
		</div>
		<div class="col-md-6">
			<button class="btn btn-primary pull-right createKey pull-right">{\App\Language::translate('LBL_ADD_APPLICATION',$QUALIFIED_MODULE)}</button>
		</div>
	</div>
	<div class="configContainer">
{/strip}
