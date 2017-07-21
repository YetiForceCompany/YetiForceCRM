{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<div class="widget_header row">
	<div class="col-xs-12">
		{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
	</div>
</div>
<div id="breadcrumb">
	<ul class="crumbs marginLeftZero">
		<li class="first step {if $STEP eq '1'}active{/if}" style="z-index:9" id="Step1">
			<a>
				<span class="stepNum">1</span>
				<span class="stepText">{\App\Language::translate('LBL_FILL_BASE_DATA',$QUALIFIED_MODULE)}</span>
			</a>
		</li>
		<li style="z-index:8" class="step {if $STEP eq '2'}active{/if}" id="Step2">
			<a>
				<span class="stepNum">2</span>
				<span class="stepText">{\App\Language::translate('ADD_CONDITIONS',$QUALIFIED_MODULE)}</span>
			</a>
		</li>
		<li style="z-index:8" class="step {if $STEP eq '3'}active{/if}" id="Step3">
			<a>
				<span class="stepNum">3</span>
				<span class="stepText">{\App\Language::translate('ADD_ACTIONS',$QUALIFIED_MODULE)}</span>
			</a>
		</li>
	</ul>
</div>
<div class="clearfix"></div>
