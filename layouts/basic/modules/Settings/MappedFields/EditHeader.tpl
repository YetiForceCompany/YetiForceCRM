{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="o-breadcrumb widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="editContainer col-md-12 paddingLRZero">
		<div id="breadcrumb">
			<ul class="crumbs marginLeftZero">
				<li class="first step zIndex8" id="step1">
					<a>
						<span class="stepNum">1</span>
						<span class="stepText">{\App\Language::translate('LBL_MF_SETTINGS',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
				<li class="step zIndex7" id="step2">
					<a>
						<span class="stepNum">2</span>
						<span class="stepText">{\App\Language::translate('LBL_MAPPING_LIST',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
				<li class="step zIndex3" id="step3">
					<a>
						<span class="stepNum">3</span>
						<span class="stepText">{\App\Language::translate('LBL_EXCEPTIONS',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
				<li class="step zIndex2" id="step4">
					<a>
						<span class="stepNum">4</span>
						<span class="stepText">{\App\Language::translate('LBL_PERMISSIONS',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
			</ul>
		</div>
		<div class="clearfix"></div>
	</div>
{/strip}
