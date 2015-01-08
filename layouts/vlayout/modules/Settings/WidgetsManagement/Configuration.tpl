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
		
	<div class=" tab-pane active mandatory" id="mandatoryWidgets" data-save="mandatory">
				<div class="pull-left">
					<button type="button" class="btn addCondition"><i class="icon-plus"></i>&nbsp;{vtranslate('LBL_ADD_CONDITION', $MODULENAME)}</button>
				</div>
				<div class="pull-right">
					<button type="button" class="saveCondition btn btn-success overlap {if !$MANDATORY_WIDGETS} hide {/if}" style="margin-right:0px;">{vtranslate('LBL_SAVE', $MODULENAME)}</button>
				</div>
				
				<br /><br />
				<table class="table table-bordered blockContainer showInlineTable condition overlap {if !$MANDATORY_WIDGETS} hide {/if} " style="vertical-align: middle;">
						<tr>
							<th style="background-color: #0065a6; text-align:center;">{vtranslate('LBL_ROLE', $MODULENAME)}</th>
							<th style="background-color: #0065a6; text-align:center;">{vtranslate('LBL_WIDGET', $MODULENAME)}</th>
							<th style="background-color: #0065a6; text-align:center;"></th>
						</tr>
						<!-- hide element -->
						<tr class="hide copyRow">
							<td style="text-align:center;" class="span3">
								<div class="pull-left">           
									<select class="role span3" name="role" style="margin-bottom:0px;" >
										<option>{vtranslate('LBL_CHOISE_ROLE',$MODULENAME)}</option>
										{foreach from=$ROLES item=ROLE}
											<option value="{$ROLE.roleid}">{vtranslate($ROLE.rolename,$MODULENAME)}</option>
										{/foreach}
									</select>
								</div> 
							</td>
							<td class="">
								<div class="pull-left" style="margin-left:5px;">            
									<select multiple class="select span8"name="widgets[]" placeholder="{vtranslate('LBL_CLICK_TO_SELECT_WIDGETS', $MODULENAME)}">
										{foreach from=$WIDGETS item=WIDGET}
											{if $WIDGET->getTitle() eq 'Mini List' || $WIDGET->getTitle() eq 'Notebook'}
												{continue}
											{/if}
											<option value="{$WIDGET->get('linkid')}">{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</option>
										{/foreach}
									</select>
								</div>
							</td>
							<td nowrap class="span1" style="text-align:center;">
								<i class="icon-trash deleteRecordButton alignMiddle cursorPointer " title="{vtranslate('LBL_DELETE', $MODULE)}" ></i>
							</td>
						</tr>
						<!-- end hide element -->
							<input class="hide" name="oldWidgets" value='{Zend_Json::encode($MANDATORY_WIDGETS)}'>
							{foreach from = $MANDATORY_WIDGETS item = widgetsRow key = roleRow}
								<tr class="rowtr">
									<td class="span3" style="text-align:center;">
										<div class="pull-left" >           
											<select class="role  hide" name="role" style="margin-bottom:0px;" >
												{foreach from=$ROLES item=ROLE}
													<option value="{$ROLE.roleid}" {if $roleRow eq $ROLE.roleid} selected {/if}>{vtranslate($ROLE.rolename,$MODULENAME)}</option>
												{/foreach}
											</select>
										</div> 
										{foreach from=$ROLES item=ROLE}
											{if $roleRow eq $ROLE.roleid} <big>{vtranslate($ROLE.rolename,$MODULENAME)}</big>{/if}
										{/foreach}
									</td>
									<td class="">
										<div class="pull-left" style="margin-left:5px;">            
											<select multiple class="select2 span8" name="widgets[]" placeholder="{vtranslate('LBL_CLICK_TO_SELECT_WIDGETS', $MODULENAME)}">
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
									<td nowrap class="span1" style="text-align:center;">
										<i class="icon-trash deleteRecordButton alignMiddle cursorPointer " title="{vtranslate('LBL_DELETE', $MODULE)}"></i>
									</td>
								</tr>
							{/foreach}
				</table>
	</div>
	
	{* settings module form *}
	<div class=" tab-pane inactive" id="inactiveWidgets" data-save="inactive">
					<div class="pull-left">
						<button type="button" class="btn addCondition"><i class="icon-plus"></i>&nbsp;{vtranslate('LBL_ADD_CONDITION', $MODULENAME)}</button>
					</div>
					<div class="pull-right">
						<button type="button" class="saveCondition btn btn-success overlap  {if !$INACTIVE_WIDGETS} hide {/if}" style="margin-right:0px;">{vtranslate('LBL_SAVE', $MODULENAME)}</button>
					</div>
					<br /><br />
					<table class="table table-bordered blockContainer showInlineTable condition overlap {if !$INACTIVE_WIDGETS} hide {/if}" >
						<tr>
							<th style="background-color: #0065a6; text-align:center;">{vtranslate('LBL_ROLE', $MODULENAME)} </th>
							<th style="background-color: #0065a6; text-align:center;">{vtranslate('LBL_WIDGET', $MODULENAME)}</th>
							<th style="background-color: #0065a6; text-align:center;"></th>
						</tr>
						<!-- hide element -->
						<tr class="hide copyRow">
							<td style="text-align:center;" class="span3">
								<div class="pull-left">           
									<select class="role span3" name="role" style="margin-bottom:0px;" >
										<option value='0'>{vtranslate('LBL_CHOISE_ROLE',$MODULENAME)}</option>
										{foreach from=$ROLES item=ROLE}
											<option value="{$ROLE.roleid}">{vtranslate($ROLE.rolename,$MODULENAME)}</option>
										{/foreach}
									</select>
								</div> 
							</td>
							<td class="">
								<div class="pull-left" style="margin-left:5px;">            
									<select multiple class="select span8" name="widgets[]" placeholder="{vtranslate('LBL_CLICK_TO_SELECT_WIDGETS', $MODULENAME)}">
										{foreach from=$WIDGETS item=WIDGET}
											{assign var=LINKID value=$WIDGET->get('linkid')}
											<option value="{$LINKID}">{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</option>
										{/foreach}
									</select>
								</div>
							</td>
							<td nowrap class="span1" style="text-align:center;">
								<i class="icon-trash deleteRecordButton alignMiddle cursorPointer " title="{vtranslate('LBL_DELETE', $MODULE)}" ></i>
							</td>
						</tr>
						<!-- end hide element -->
							<input class="hide" name="oldWidgets" value='{Zend_Json::encode($INACTIVE_WIDGETS)}'>
							{foreach from = $INACTIVE_WIDGETS item = widgetsRow key = roleRow}
								<tr class="rowtr">
									<td class="span3" style="text-align:center;">
										<div class="pull-left" >           
											<select class="role  hide" name="role" style="margin-bottom:0px;" >
												{foreach from=$ROLES item=ROLE}
													<option value="{$ROLE.roleid}" {if $roleRow eq $ROLE.roleid} selected {/if}>{vtranslate($ROLE.rolename,$MODULENAME)}</option>
												{/foreach}
											</select>
										</div> 
										{foreach from=$ROLES item=ROLE}
											{if $roleRow eq $ROLE.roleid} <big>{vtranslate($ROLE.rolename,$MODULENAME)}</big>{/if}
										{/foreach}
									</td>
									<td class="">
										<div class="pull-left" style="margin-left:5px;">            
											<select multiple class="select2 span8" name="widgets[]" placeholder="{vtranslate('LBL_CLICK_TO_SELECT_WIDGETS', $MODULENAME)}">
												{foreach from=$WIDGETS item=WIDGET}
													{assign var=LINKID value=$WIDGET->get('linkid')}										
													<option value="{$LINKID}" {if in_array($LINKID, $widgetsRow)} selected {/if}>{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</option>
												{/foreach}
											</select>
										</div>
									</td>
									<td nowrap class="span1" style="text-align:center;">
										<i class="icon-trash deleteRecordButton cursorPointer alignMiddle" title="{vtranslate('LBL_DELETE', $MODULE)}" ></i>
									</td>
								</tr>
							{/foreach}
					</table>
    </div>
</div>
</div>

