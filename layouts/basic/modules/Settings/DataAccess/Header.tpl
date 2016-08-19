{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
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
				<span class="stepText">{vtranslate('LBL_FILL_BASE_DATA',$QUALIFIED_MODULE)}</span>
			</a>
		</li>
		<li style="z-index:8" class="step {if $STEP eq '2'}active{/if}" id="Step2">
			<a>
				<span class="stepNum">2</span>
				<span class="stepText">{vtranslate('ADD_CONDITIONS',$QUALIFIED_MODULE)}</span>
			</a>
		</li>
		<li style="z-index:8" class="step {if $STEP eq '3'}active{/if}" id="Step3">
			<a>
				<span class="stepNum">3</span>
				<span class="stepText">{vtranslate('ADD_ACTIONS',$QUALIFIED_MODULE)}</span>
			</a>
		</li>
	</ul>
</div>
<div class="clearfix"></div>
