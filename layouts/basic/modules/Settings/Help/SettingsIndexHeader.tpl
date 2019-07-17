{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Help-SettingsIndexHeader -->
	<div class="widget_header row ">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="row m-0 mt-2">
		<ul class="nav nav-tabs massEditTabs js-tabs mx-0" data-js="click">
			<li class="nav-item" data-mode="github">
				<a class="active nav-link" href="#" data-toggle="tab">
					<span class="fab fa-github fa-fw mr-1"></span>
					{\App\Language::translate('LBL_GITHUB', $QUALIFIED_MODULE)}
				</a>
			</li>
		</ul>
	</div>
	<div class="indexContainer"></div>
	<!-- /tpl-Settings-Help-SettingsIndexHeader -->
{/strip}
