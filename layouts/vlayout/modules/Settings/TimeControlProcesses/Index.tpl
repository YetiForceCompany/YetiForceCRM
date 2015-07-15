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
	<h3>{vtranslate('LBL_TIMECONTROL_PROCESSES', $QUALIFIED_MODULE)}</h3>
	<h5>{vtranslate('LBL_TIMECONTROL_PROCESSES_DESCRIPTION', $QUALIFIED_MODULE)}</h5>
	<hr>

	<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
		<li class="active"><a href="#general" data-toggle="tab">{vtranslate('LBL_GENERAL_SETTINGS', $QUALIFIED_MODULE)}</a></li>
	</ul>
	<br />
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
    </div>
</div>
