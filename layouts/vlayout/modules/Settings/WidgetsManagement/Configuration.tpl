{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}
 
<style>
.fieldDetailsForm .zeroOpacity{
display: none;
}
.visibility{
visibility: hidden;
}
.paddingNoTop20{
padding: 20px 20px 20px 20px;
}
</style>
<div class="container-fluid" id="widgetsManagementEditorContainer">
		<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
		<div class="widget_header row-fluid">
			<div class="span8">
				<h3>{vtranslate('LBL_WIDGETS_MANAGEMENT', $MODULENAME)}</h3>
				{vtranslate('LBL_WIDGETS_MANAGEMENT_DESCRIPTION', $MODULENAME)}
			</div>
			<div class="span4">
				<div class="pull-right">
					<select class="select2 span3" name="widgetsManagementEditorModules">
						{foreach item=mouleName from=$SUPPORTED_MODULES}
							<option value="{$mouleName}" {if $mouleName eq $SELECTED_MODULE_NAME} selected {/if}>{vtranslate($mouleName, $MODULENAME)}</option>
						{/foreach}
				</select>
				</div>
			</div>
		</div>
		<hr>
		
		
<div class="contents tabbable">
	
	<ul class="nav nav-tabs layoutTabs massEditTabs">
		<li class="active"><a href="#mandatoryWidgets" data-toggle="tab">{vtranslate('LBL_MANDATORY_WIDGETS', $MODULENAME)}</a></li>
		<li><a data-toggle="tab" href="#inactiveWidgets">{vtranslate('LBL_INACTIVE_WIDGETS', $MODULENAME)}</a></li>
	</ul>
	<div class="tab-content layoutContent paddingNoTop20 themeTableColor overflowVisible">
		
	<div class=" tab-pane active " id="mandatoryWidgets" style="min-height:500px">
		<table>
			<tr>
			<td class="mandatory" valign="top" data-save="mandatory">
				<div class="pull-left">
					<button type="button" class="btn addCondition">{vtranslate('LBL_ADD_CONDITION', $MODULENAME)}</button>
				</div>
				<div class="pull-right">
					<button type="button" class="saveCondition btn btn-success">{vtranslate('LBL_SAVE', $MODULENAME)}</button>
				</div>
				
				<br /><br />
				<div>
					<table class="table table-bordered blockContainer showInlineTable condition" style="vertical-align: middle;">
						<tr>
							<th style="background-color: #0065a6; text-align:center;">{vtranslate('LBL_CHOISE_ROLE', $MODULENAME)}</th>
							<th style="background-color: #0065a6; text-align:center;">{vtranslate('LBL_CHOISE_WIDGET', $MODULENAME)}</th>
							<th style="background-color: #0065a6; text-align:center;"></th>
						</tr>
						<!-- hide element -->
						<tr class="hide copyRow">
							<td class="span12" >
								<div class="pull-left" style="margin-left:5px;">           
									<select class="role" name="role" style="margin-bottom:0px;">
										{foreach from=$ROLES item=ROLE}
											<option value="{$ROLE.roleid}">{vtranslate($ROLE.rolename,$MODULENAME)}</option>
										{/foreach}
									</select>
								</div> 
							</td>
							<td class="span12">
								<div class="pull-left" style="margin-left:5px;">            
									<select multiple class="select"name="widgets[]" style="width:300px;">
										{foreach from=$WIDGETS item=WIDGET}
											{if $WIDGET->getTitle() eq 'Mini List' || $WIDGET->getTitle() eq 'Notebook'}
												{continue}
											{/if}
											<option value="{$WIDGET->get('linkid')}">{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</option>
										{/foreach}
									</select>
								</div>
							</td>
							<td nowrap style="min-width:20px;">
								<i class="icon-trash deleteRecordButton cursorPointer" title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i>
							</td>
						</tr>
						<!-- end hide element -->
						{if $MANDATORY_WIDGETS}
							<input class="hide" name="oldWidgets" value='{Zend_Json::encode($MANDATORY_WIDGETS)}'>
							{foreach from = $MANDATORY_WIDGETS item = widgetsRow key = roleRow}
								<tr class="rowtr">
									<td class="span12">
										<div class="pull-left" style="margin-left:5px;">           
											<select class="role" name="role" style="margin-bottom:0px;">
												{foreach from=$ROLES item=ROLE}
													<option value="{$ROLE.roleid}" {if $roleRow eq $ROLE.roleid} selected {/if}>{vtranslate($ROLE.rolename,$MODULENAME)}</option>
												{/foreach}
											</select>
										</div> 
									</td>
									<td class="span12">
										<div class="pull-left" style="margin-left:5px;">            
											<select multiple class="select2" name="widgets[]" style="width:300px;">
												{foreach from=$WIDGETS item=WIDGET}
													{if $WIDGET->getTitle() eq 'Mini List' || $WIDGET->getTitle() eq 'Notebook'}
														{continue}
													{/if}
													{assign var=LINKID value=$WIDGET->get('linkid')}
													<option value="{$LINKID}" {if in_array($LINKID, $widgetsRow)} selected {/if} >{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</option>
												{/foreach}
											</select>
										</div>
									</td>
									<td nowrap style="min-width:20px;">
										<i class="icon-trash deleteRecordButton cursorPointer" title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i>
									</td>
								</tr>
							{/foreach}
						{else}
							<tr class="rowtr">
								<td class="span12">
									<div class="pull-left" style="margin-left:5px;">           
										<select class="role" name="role" style="margin-bottom:0px;">
											{foreach from=$ROLES item=ROLE}
												<option value="{$ROLE.roleid}">{vtranslate($ROLE.rolename,$MODULENAME)}</option>
											{/foreach}
										</select>
									</div> 
								</td>
								<td class="span12">
									<div class="pull-left" style="margin-left:5px;">            
										<select multiple class="select2" name="widgets[]" style="width:300px;">
											{foreach from=$WIDGETS item=WIDGET}
												{if $WIDGET->getTitle() eq 'Mini List' || $WIDGET->getTitle() eq 'Notebook'}
													{continue}
												{/if}
												{assign var=LINKID value=$WIDGET->get('linkid')}
												<option value="{$LINKID}">{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</option>
											{/foreach}
										</select>
									</div>
								</td>
								<td nowrap style="min-width:20px;">
									<i class="icon-trash deleteRecordButton cursorPointer" title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i>
								</td>
							</tr>
						{/if}
					</table>
				</div>
			</td>
			</tr>
		</table>
	</div>
	
	{* settings module form *}
	<div class=" tab-pane " id="inactiveWidgets" style="min-height:500px">
		<table>
			<tr>
			<td class="inactive" valign="top" data-save="inactive">
					<div class="pull-left">
						<button type="button" class="btn addCondition">{vtranslate('LBL_ADD_CONDITION', $MODULENAME)}</button>
					</div>
					<div class="pull-right">
						<button type="button" class="saveCondition btn btn-success">{vtranslate('LBL_SAVE', $MODULENAME)}</button>
					</div>
					<br /><br />
					<table class="table table-bordered blockContainer showInlineTable condition" >
						<tr>
							<th style="background-color: #0065a6; text-align:center;">{vtranslate('LBL_CHOISE_ROLE', $MODULENAME)} </th>
							<th style="background-color: #0065a6; text-align:center;">{vtranslate('LBL_CHOISE_WIDGET', $MODULENAME)}</th>
							<th style="background-color: #0065a6; text-align:center;"></th>
						</tr>
						<!-- hide element -->
						<tr class="hide copyRow">
							 <td class="span12">
								<div class="pull-left" style="margin-left:5px;">           
									<select class="role" name="role" style="margin-bottom:0px;">
										{foreach from=$ROLES item=ROLE}
											<option value="{$ROLE.roleid}">{vtranslate($ROLE.rolename,$MODULENAME)}</option>
										{/foreach}
									</select>
								</div> 
							</td>
							<td class="span12">
								<div class="pull-left" style="margin-left:5px;">            
									<select multiple class="select" name="widgets[]" style="width:300px;">
										{foreach from=$WIDGETS item=WIDGET}
											{assign var=LINKID value=$WIDGET->get('linkid')}
											<option value="{$LINKID}">{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</option>
										{/foreach}
									</select>
								</div>
							</td>
							<td nowrap style="min-width:20px;">
								<i class="icon-trash deleteRecordButton cursorPointer" title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i>
							</td>
						</tr>
						<!-- end hide element -->
						{if $INACTIVE_WIDGETS}
							<input class="hide" name="oldWidgets" value='{Zend_Json::encode($INACTIVE_WIDGETS)}'>
							{foreach from = $INACTIVE_WIDGETS item = widgetsRow key = roleRow}
								<tr class="rowtr">
									 <td class="span12">
										<div class="pull-left" style="margin-left:5px;">           
											<select class="role" name="role" style="margin-bottom:0px;">
												{foreach from=$ROLES item=ROLE}
													<option value="{$ROLE.roleid}" {if $roleRow eq $ROLE.roleid} selected {/if}>{vtranslate($ROLE.rolename,$MODULENAME)}</option>
												{/foreach}
											</select>
										</div>
									</td>
									<td class="span12">
										<div class="pull-left" style="margin-left:5px;">            
											<select multiple class="select2" name="widgets[]" style="width:300px;">
												{foreach from=$WIDGETS item=WIDGET}
													{assign var=LINKID value=$WIDGET->get('linkid')}										
													<option value="{$LINKID}" {if in_array($LINKID, $widgetsRow)} selected {/if}>{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</option>
												{/foreach}
											</select>
										</div>
									</td>
									<td nowrap style="min-width:20px;">
										<i class="icon-trash deleteRecordButton cursorPointer" title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i>
									</td>
								</tr>
							{/foreach}
						{else}
							<tr class="rowtr">
								 <td class="span12">
									<div class="pull-left" style="margin-left:5px;">           
										<select class="role" name="role" style="margin-bottom:0px;">
											{foreach from=$ROLES item=ROLE}
												<option value="{$ROLE.roleid}">{vtranslate($ROLE.rolename,$MODULENAME)}</option>
											{/foreach}
										</select>
									</div>
								</td>
								<td class="span12">
									<div class="pull-left" style="margin-left:5px;">            
										<select multiple class="select2" name="widgets[]" style="width:300px;">
											{foreach from=$WIDGETS item=WIDGET}
												{assign var=LINKID value=$WIDGET->get('linkid')}										
												<option value="{$LINKID}">{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</option>
											{/foreach}
										</select>
									</div>
								</td>
								<td nowrap style="min-width:20px;">
									<i class="icon-trash deleteRecordButton cursorPointer" title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i>
								</td>
							</tr>
						{/if}
					</table>
				</td>
			</tr>
		</table>
    </div>
</div>
</div>

