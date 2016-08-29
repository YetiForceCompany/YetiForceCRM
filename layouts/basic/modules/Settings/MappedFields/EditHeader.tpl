{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<div class="editContainer col-md-12 paddingLRZero">
		<div id="breadcrumb">
			<ul class="crumbs marginLeftZero">
				<li class="first step zIndex8" id="step1">
					<a>
						<span class="stepNum">1</span>
						<span class="stepText">{vtranslate('LBL_MF_SETTINGS',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
				<li class="step zIndex7" id="step2">
					<a>
						<span class="stepNum">2</span>
						<span class="stepText">{vtranslate('LBL_MAPPING_LIST',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
				<li class="step zIndex3" id="step3">
					<a>
						<span class="stepNum">3</span>
						<span class="stepText">{vtranslate('LBL_EXCEPTIONS',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
				<li class="step zIndex2" id="step4">
					<a>
						<span class="stepNum">4</span>
						<span class="stepText">{vtranslate('LBL_PERMISSIONS',$QUALIFIED_MODULE)}</span>
					</a>
				</li>
			</ul>
		</div>
		<div class="clearfix"></div>
	</div>
{/strip}
