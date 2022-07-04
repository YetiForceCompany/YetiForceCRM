{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WebserviceApps-IndexPreProcess -->
	<div class="o-breadcrumb widget_header row align-items-center mb-2">
		<div class="col-md-8">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
		<div class="col-md-4 d-flex justify-content-lg-end pr-3">
			<a href="https://doc.yetiforce.com/api/" target="_blank" class="btn btn-outline-info float-right mr-3 js-popover-tooltip" data-content="{App\Language::translate('BTM_GOTO_YETIFORCE_DOCUMENTATION')}" rel="noreferrer noopener" data-js="popover">
				<span class="mdi mdi-book-open-page-variant u-fs-lg"></span>
			</a>
			<a href="index.php?module=WebserviceUsers&view=List&parent=Settings" class="btn btn-success float-right mr-3">
				<span class="adminIcon-webservice-users u-fs-lg mr-2"></span>
				{\App\Language::translate('WebserviceUsers','Settings:WebserviceUsers')}
			</a>
			<button class="btn btn-primary createKey"><span class="fas fa-plus mr-1"></span>{\App\Language::translate('LBL_ADD_APPLICATION',$QUALIFIED_MODULE)}</button>
		</div>
	</div>
	<div class="configContainer">
		<!-- /tpl-Settings-WebserviceApps-IndexPreProcess -->
{/strip}
