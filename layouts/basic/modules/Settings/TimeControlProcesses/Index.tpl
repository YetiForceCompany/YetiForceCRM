{*/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/*}

<div class="processesContainer">
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			<h5>{vtranslate('LBL_TIMECONTROL_PROCESSES_DESCRIPTION', $QUALIFIED_MODULE)}</h5>
		</div>
	</div>
	<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
		<li class="active"><a href="#general" data-toggle="tab">{vtranslate('LBL_GENERAL_SETTINGS', $QUALIFIED_MODULE)}</a></li>
		<li><a href="#timeControlWidget" data-toggle="tab">{vtranslate('LBL_TIME_CONTROL_WIDGET', $QUALIFIED_MODULE)}</a></li>
	</ul>
	<br/>
	<div class="tab-content">
		<div class='editViewContainer tab-pane active' id="general" data-type="general">
			{assign var=GENERAL_FIELDS value=$MODULE_MODEL->get('general')}
			<div class="" data-toggle="buttons">
				<label class="btn {if $GENERAL_FIELDS.oneDay eq 'true'}btn-success active{else}btn-default{/if} btn-block">
					<input autocomplete="off" type="checkbox" name="oneDay" {if $GENERAL_FIELDS.oneDay eq 'true'}checked{/if}>{vtranslate('LBL_ONEDAY_VALID', $QUALIFIED_MODULE)}
					<span class="glyphicon {if $GENERAL_FIELDS.oneDay eq 'true'}glyphicon-check{else}glyphicon-unchecked{/if} pull-left"></span>
				</label>
				<label class="btn {if $GENERAL_FIELDS.timeOverlap eq 'true'}btn-success active{else}btn-default{/if} btn-block">
					<input autocomplete="off" type="checkbox" name="timeOverlap" {if $GENERAL_FIELDS.timeOverlap eq 'true'}checked{/if}>{vtranslate('LBL_TIMEOVERLAP_VALID', $QUALIFIED_MODULE)}
					<span class="glyphicon {if $GENERAL_FIELDS.timeOverlap eq 'true'}glyphicon-check{else}glyphicon-unchecked{/if} pull-left"></span>
				</label>
			</div>
		</div>
		<div class="tab-pane editViewContainer" id="timeControlWidget" data-type="timeControlWidget">
			<div class="alert alert-info" role="alert">{vtranslate('LBL_TCW_INFO', $QUALIFIED_MODULE)}</div>
			{assign var=TCW_FIELDS value=$MODULE_MODEL->get('timeControlWidget')}
			<div class="" data-toggle="buttons">
				<label class="btn {if $TCW_FIELDS.holidays eq 'true'}btn-success active{else}btn-default{/if} btn-block">
					<input autocomplete="off" type="checkbox" name="holidays" {if $TCW_FIELDS.holidays eq 'true'}checked{/if}> {vtranslate('LBL_HOLIDAYS', $QUALIFIED_MODULE)}
					<span class="glyphicon {if $TCW_FIELDS.holidays eq 'true'}glyphicon-check{else}glyphicon-unchecked{/if} pull-left"></span>
				</label>
				<label class="btn {if $TCW_FIELDS.workingDays eq 'true'}btn-success active{else}btn-default{/if} btn-block">
					<input autocomplete="off" type="checkbox" name="workingDays" {if $TCW_FIELDS.workingDays eq 'true'}checked{/if}> {vtranslate('LBL_WORKING_DAYS', $QUALIFIED_MODULE)}
					<span class="glyphicon {if $TCW_FIELDS.workingDays eq 'true'}glyphicon-check{else}glyphicon-unchecked{/if} pull-left"></span>
				</label>
				<label class="btn {if $TCW_FIELDS.workingTime eq 'true'}btn-success active{else}btn-default{/if} btn-block">
					<input autocomplete="off" type="checkbox" name="workingTime" {if $TCW_FIELDS.workingTime eq 'true'}checked{/if}> {vtranslate('LBL_WORKING_TIME', $QUALIFIED_MODULE)}
					<span class="glyphicon {if $TCW_FIELDS.workingTime eq 'true'}glyphicon-check{else}glyphicon-unchecked{/if} pull-left"></span>
				</label>
			</div>
		
		</div>
	</div>
</div>
