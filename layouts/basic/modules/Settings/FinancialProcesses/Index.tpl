{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="supportProcessesContainer">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<ul id="tabs" class="nav nav-tabs mt-2 mr-0" data-tabs="tabs">
			<li class="nav-item"><a class="nav-link active" href="#configuration" data-toggle="tab">{\App\Language::translate('LBL_GENERAL', $QUALIFIED_MODULE)} </a></li>
		</ul>
		<br />
		<div class="tab-content">
			<div class='editViewContainer tab-pane active' id="configuration">
			</div>
		</div>
	</div>
{/strip}
