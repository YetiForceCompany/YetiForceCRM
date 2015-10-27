{*/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/*}

<div class=" supportProcessesContainer" style="margin-top:10px;">
	<h3>{vtranslate('LBL_SUPPORT_PROCESSES', $QUALIFIED_MODULE)}</h3>&nbsp;<hr>
	<ul id="tabs" class="nav nav-tabs " data-tabs="tabs">
		<li class="active"><a href="#general_configuration" data-toggle="tab">{vtranslate('LBL_GENERAL_CONFIGURATION', $QUALIFIED_MODULE)} </a></li>
	</ul>
	<br />
	<div class="tab-content">
		<div class='editViewContainer tab-pane active' id="general_configuration">
			<table class="table table-bordered table-condensed themeTableColor userTable">
				<thead>
					<tr class="blockHeader" >
						<th class="mediumWidthType">
							<span>{vtranslate('LBL_INFO', $QUALIFIED_MODULE)}</span>
						</th>
						<th class="mediumWidthType">
							<span>{vtranslate('LBL_TYPE', $QUALIFIED_MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr data-id="{$ITEM.user_id}">
						<td><label>{vtranslate('LBL_TICKET_STATUS_INFO', $QUALIFIED_MODULE)}</label></td>
						<td class="col-md-6">
							{assign var=TICKETSTATUSNOTMODIFY value=$TICKETSTATUSNOTMODIFY}
							<select class="chzn-select status" multiple name="status" style="width: 500px;">
								{foreach  item=ITEM from=$TICKETSTATUS}
									<option value="{$ITEM['status']}" {if in_array($ITEM['status'], $TICKETSTATUSNOTMODIFY)} selected {/if}  >{$ITEM['statusTranslate']}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<button class="btn btn-success saveButton pull-right" type="submit" id='saveConfig' title="{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}" style="margin-top:10px;"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
		</div>	
	</div>
</div>
