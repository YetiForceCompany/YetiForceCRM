{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}

 <div class="supportProcessesContainer">
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}	
			{vtranslate('LBL_FINANCIAL_PROCESSES_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
	<ul id="tabs" class="nav nav-tabs " data-tabs="tabs">
		<li class="active"><a href="#configuration" data-toggle="tab">{vtranslate('LBL_GENERAL', $QUALIFIED_MODULE)} </a></li>
	</ul>
	<br />
	<div class="tab-content">
		<div class='editViewContainer tab-pane active' id="configuration">
		</div>
	</div>
</div>
