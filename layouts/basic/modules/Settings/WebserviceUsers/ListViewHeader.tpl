{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class='widget_header row '>
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
	</div>
	<div class="row no-margin">
		<ul id="tabs" class="nav nav-tabs " data-tabs="tabs">
			{foreach item=VALUE from=Settings_WebserviceApps_Module_Model::getTypes() name=typeLoop}
				<li class="tabApi{if $smarty.foreach.typeLoop.first} active{/if}" data-typeapi="{$VALUE}">
					<a data-toggle="tab"><strong>{\App\Language::translate($VALUE, $QUALIFIED_MODULE)}</strong></a>
				</li>
			{/foreach}
		</ul>
	</div>
	<div class="tab-content listViewContent">
	{/strip}
