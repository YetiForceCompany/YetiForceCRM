{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class="listViewPageDiv">
	<div class="o-breadcrumb widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
	</div>
	<ul id="tabs" class="nav nav-tabs mt-2 mr-0" data-tabs="tabs">
		{foreach item=VALUE from=Settings_WebserviceApps_Module_Model::getTypes() name=typeLoop}
			{if $VALUE neq 'Payments'}
				<li class="tabApi nav-item" data-typeapi="{$VALUE}">
					<a class="nav-link {if $smarty.foreach.typeLoop.first} active{/if} " data-toggle="tab"><strong>{\App\Language::translate($VALUE, $QUALIFIED_MODULE)}</strong></a>
				</li>
			{/if}
		{/foreach}
	</ul>
	<div class="tab-content listViewContent">
	{/strip}
