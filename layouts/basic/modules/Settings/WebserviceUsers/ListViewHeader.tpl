{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WebserviceUsers-ListViewHeader -->
	<div class="listViewPageDiv">
		<div class="o-breadcrumb widget_header row align-items-center mb-2">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-4 d-flex justify-content-lg-end">
				<a href="index.php?module=WebserviceApps&view=Index&parent=Settings" class="btn btn-success float-right">
					<span class="adminIcon-webservice-apps u-fs-lg mr-2"></span>
					{\App\Language::translate('LBL_WEBSERVICE_APPS','Settings:WebserviceApps')}
				</a>
			</div>
		</div>
		<ul id="tabs" class="nav nav-tabs mt-2 mr-0" data-tabs="tabs">
			{foreach item=VALUE from=\Api\Core\Containers::$listTab name=typeLoop}
				<li class="tabApi nav-item" data-typeapi="{$VALUE}">
					<a class="nav-link {if $TYPE_API === $VALUE} active{/if}" data-toggle="tab" href="#">
						<strong>{\App\Language::translate($VALUE, 'Settings:WebserviceApps')}</strong>
						{if $VALUE === 'WebservicePremium'}
							<span class="yfi-premium color-red-600 ml-2" title="{\App\Language::translate('LBL_PAID_FUNCTIONALITY', 'Settings::YetiForce')}"></span>
						{/if}
					</a>
				</li>
			{/foreach}
		</ul>
		<div class="tab-content listViewContent">
			<!-- /tpl-Settings-WebserviceUsers-ListViewHeader -->
{/strip}
