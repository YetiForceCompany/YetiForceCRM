{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Mail-Config -->
	<div class="verticalScroll">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<ul class="nav nav-tabs mt-2 mb-2" role="tabs">
			<li class="nav-item"><a class="nav-link active" href="#mailIcon" data-toggle="tab" role="tab">{\App\Language::translate('LBL_MAIL_ICON_CONFIG', $QUALIFIED_MODULE)}</a></li>
			<li class="nav-item"><a class="nav-link" href="#signature" data-toggle="tab" role="tab">{\App\Language::translate('LBL_SIGNATURE', $QUALIFIED_MODULE)}</a></li>
			<li class="nav-item"><a class="nav-link" href="#scanner" data-toggle="tab" role="tab">{\App\Language::translate('LBL_MAIL_SCANNER', $QUALIFIED_MODULE)}</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane fade show active ml-3" id="mailIcon" role="tabpanel" aria-labelledby="home-tab">
				{include file=\App\Layout::getTemplatePath('TabContent.tpl', $QUALIFIED_MODULE) CONFIG_NAME='mailIcon'}
			</div>
			<div class="tab-pane fade" id="signature" role="tabpanel">
				{include file=\App\Layout::getTemplatePath('TabContent.tpl', $QUALIFIED_MODULE) CONFIG_NAME='signature'}
			</div>
			<div class="tab-pane fade" id="scanner" role="tabpanel">
				{include file=\App\Layout::getTemplatePath('TabContent.tpl', $QUALIFIED_MODULE) CONFIG_NAME='scanner'}
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-Mail-Config -->
{/strip}
