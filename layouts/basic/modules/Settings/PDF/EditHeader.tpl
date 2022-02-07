{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="row widget_header mb-2">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="editContainer">
		<div id="breadcrumb">
			<ul class="crumbs marginLeftZero">
				<li class="first step zIndex1" id="step1">
					<a>
						<span class="stepNum">1</span>
						<span class="stepText">{\App\Language::translate('LBL_DOCUMENT_BASIC_OPTIONS',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
				<li class="step zIndex2" id="step2">
					<a>
						<span class="stepNum">2</span>
						<span class="stepText">{\App\Language::translate('LBL_DOCUMENT_CONTENT',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
				<li class="step zIndex3" id="step3">
					<a>
						<span class="stepNum">3</span>
						<span class="stepText">{\App\Language::translate('LBL_DOCUMENT_EXCEPTIONS',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
			</ul>
		</div>
		<div class="clearfix"></div>
	</div>
{/strip}
