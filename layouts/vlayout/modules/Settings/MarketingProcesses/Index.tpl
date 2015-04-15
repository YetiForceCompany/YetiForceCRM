{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}

 <div class="container-fluid supportProcessesContainer" style="margin-top:10px;">
 	<h3>{vtranslate('LBL_MARKETING_PROCESSES', $QUALIFIED_MODULE)}</h3>&nbsp;<hr>
	{vtranslate('LBL_MARKETING_PROCESSES_DESCRIPTION', $QUALIFIED_MODULE)}
	<ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs">
		<li class="active"><a href="#conversiontoaccount" data-toggle="tab">{vtranslate('LBL_CONVERSION_TO_ACCOUNT', $QUALIFIED_MODULE)} </a></li>
		<li ><a href="#lead_configuration" data-toggle="tab">{vtranslate('LBL_LEADS', $QUALIFIED_MODULE)} </a></li>
	</ul>
	<div class="tab-content">
		<div class='editViewContainer tab-pane active' id="conversiontoaccount">
			<div class="container-fluid settingsIndexPage">
				<form  method="post" action="index.php">
					<div class="control-group span5" >
						<label class="span3">{vtranslate('LBL_CONVERSION_TO_ACCOUNT',$QUALIFIED_MODULE)}</label>
						<input class="span1" type="checkbox" name="conversiontoaccount"  {if $STATE} checked {/if} />
					</div>

					<span class="alert alert-info pull-right span7">
						{vtranslate('LBL_CONVERSION_TO_ACCOUNT_INFO',$QUALIFIED_MODULE)}
					</span>
				</form>
				<span class="span12">
					<button style="margin-left: 20px;" id="saveConversionState" class="btn btn-success">{vtranslate('LBL_SAVE',$QUALIFIED_MODULE)}</button>
				</span>
				
			</div>
		</div>
		<div class='editViewContainer tab-pane' id="lead_configuration">
			<form class="form-horizontal fieldDetailsForm" method="POST">
				<input type="hidden" name="module" value="Leads" />
				<table class="table table-bordered table-condensed themeTableColor userTable">
					<thead>
						<tr class="blockHeader" >
							<th class="mediumWidthType">
								<span>{vtranslate('LBL_INFO', $QUALIFIED_MODULE)}</span>
							</th>
							<th class="mediumWidthType">
								<span>{vtranslate('LBL_VALUES', $QUALIFIED_MODULE)}</span>
							</th>
						</tr>
					</thead>
					<tbody>
						{assign var=LEAD_MODEL_PROCESS value=$PROCESSES->getInstance('Leads')}
						<tr>
							<td><label>{vtranslate('LBL_GROUPS_INFO', $QUALIFIED_MODULE)}</label></td>
							<td>
								{assign var=ALL_ACTIVEGROUP_LIST value=$USER_MODEL->getAccessibleGroups('Leads')}
								<select class="chzn-select span8" name="groups" multiple>
									{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
										<option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if in_array($OWNER_ID, $LEAD_MODEL_PROCESS->getGroups())} selected {/if} >
										{$OWNER_NAME}
										</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td><label>{vtranslate('LBL_LEAD_STATUS', $QUALIFIED_MODULE)}</label></td>
							<td>
								<select class="chzn-select span8" multiple name="leadstatus">
									{foreach  item=ITEM from=$LEADSTATUS}
										<option value="{$ITEM}" {if in_array($ITEM, $LEAD_MODEL_PROCESS->getLeadStatus())} selected {/if}  >{vtranslate($ITEM,'Leads')}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td><label>{vtranslate('LBL_CURRENTUSER_STATUS', $QUALIFIED_MODULE)}</label></td>
							<td>
								<input class="span1" type="checkbox" name="currentuser_status"  {if $LEAD_MODEL_PROCESS->get('currentuser_status')} checked {/if} />
							</td>
						</tr>
					</tbody>
				</table>
				<div class="">
					<button class="btn btn-success pull-left saveButton" name="saveButton" title="{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}" style="margin-top:10px;"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
				</div>
			</form>
		</div>
	</div>
</div>
