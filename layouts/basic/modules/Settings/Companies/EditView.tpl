{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Companies-EditView -->
	<div class="row mb-2 widget_header">
		<div class="col-12 d-flex">
            {include file=App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
    {include file=App\Layout::getTemplatePath('Form.tpl',$QUALIFIED_MODULE) MODULE_NAME=$QUALIFIED_MODULE}
	<!-- /tpl-Settings-Companies-EditView -->
{/strip}
