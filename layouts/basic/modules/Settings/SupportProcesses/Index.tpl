{*/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/*}

<div class=" supportProcessesContainer">
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<ul id="tabs" class="nav nav-tabs " data-tabs="tabs">
		<li class="active"><a href="#general_configuration" data-toggle="tab">{vtranslate('LBL_GENERAL_CONFIGURATION', $QUALIFIED_MODULE)} </a></li>
	</ul>
	<br />
	<div class="tab-content">
		<div class='editViewContainer tab-pane active' id="general_configuration">
			<table class="table tableRWD table-bordered table-condensed themeTableColor userTable">
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
					<tr data-id="{$ITEM['user_id']}">
						<td><label>{vtranslate('LBL_TICKET_STATUS_INFO', $QUALIFIED_MODULE)}</label></td>
						<td class="col-xs-6">
							<select class="chzn-select configField form-control status" multiple name="status">
								{foreach  item=STATUS from=$TICKETSTATUS}
									<option value="{$STATUS}" {if in_array($STATUS, $TICKETSTATUSNOTMODIFY)} selected {/if}  >{\App\Language::translate($STATUS, 'HelpDesk')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>	
	</div>
</div>
