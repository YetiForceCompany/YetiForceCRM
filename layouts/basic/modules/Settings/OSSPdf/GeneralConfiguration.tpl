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
{$MODULENAME='OSSPdf'}
<link rel="stylesheet" type="text/css" href="layouts/basic/modules/Settings/OSSPdf/general.css">
{literal}
	<!--[if lte IE 6]>
	<STYLE type=text/css>
	DIV.fixedLay {
			POSITION: absolute;
	}
	</STYLE>
	<![endif]-->
{/literal}
{$i=1}
{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
<hr>
<div class="" id="importModules">    
	<ul class="nav nav-tabs layoutTabs margin0px" style="border-bottom: 0px;">
		<li class="active"><a data-toggle="tab" href="#general"><strong>{vtranslate('LBL_GeneralConfiguration', 'OSSPdf')}</strong></a></li>
		<li class="relatedListTab"><a data-toggle="tab" href="#function"><strong>{vtranslate('LBL_specialfunctions_config', 'OSSPdf')}</strong></a></li>
	</ul> 
	<div class="tab-content layoutContent themeTableColor border1px overflowVisible" style="border-radius: 0px 4px 4px;">
		<div class="tab-pane active" id="general">
			<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
				<tr>
					<td class="showPanelBg" style="padding: 10px;" valign="top" width="100%">
						<br>
						<div align=center>

						</div>
						<table border=0 cellspacing=0 cellpadding=5 width=100% class="settingsSelUITopLine">
							<tr>
								<td valign=top class="small" align="left"></td>
							</tr>
						</table>


						<table  class="tableRWD createTable">
							<tr>
								<td width="450px" colspan="2"><center><b>{vtranslate('AttachInfo', 'OSSPdf')}</b></center> </td><td width="25px"></td><td><b>{vtranslate('LinkInfo', 'OSSPdf')}</b></td>
				</tr>
				{foreach item=module key=name from=$modules}
					<tr>
						<td width="300px">{$module}</td>
						<td width="150px">
							<select name="{$name}" class="chzn-select form-control">
								<optgroup label="{vtranslate('default', 'OSSPdf')}">
									<option value="default" {if $SELECTED.$name eq "default"} SELECTED {/if}>{vtranslate('LBL_default', 'OSSPdf')}</option>
								<optgroup label="{vtranslate('users', 'OSSPdf')}">
									{foreach key=id item=itemname from=$users}
										<option value="{$id}" {if $SELECTED.$name eq $id} SELECTED {/if}>{$itemname}</option>
									{/foreach}
								<optgroup label="{vtranslate('groups', 'OSSPdf')}">
									{foreach key=id item=itemname from=$groups}
										<option value="{$id}" {if $SELECTED.$name eq $id} SELECTED {/if}>{$itemname}</option>
									{/foreach}
							</select>
						</td><td width="25px"></td>
						<td width="300px">
							{if $BUTTONINFO.$name eq '0'}
								<span style="color: red;">{vtranslate('ButtonsNo', 'OSSPdf')}</span>
								<a class="linkes" href="index.php?module=OSSPdf&view=Index&parent=Settings&block={$smarty.get.block}&fieldid={$smarty.get.fieldid}&mode=create_buttons&formodule={$name}" data-mode="create_buttons" data-formodule="{$name}" >
									<img src="layouts/basic/modules/Settings/OSSPdf/delete.png" class="create" alt="{vtranslate('CreateButtons', 'OSSPdf')}" width="25" height="25" title="{vtranslate('CreateButtons', 'OSSPdf')}"></a>

							{/if}
							{if $BUTTONINFO.$name eq '1'} 
								<span style="color: green;">{vtranslate('ButtonsCreated', 'OSSPdf')}</span>
								<a class="linkes" href="index.php?module=OSSPdf&view=Index&parent=Settings&block={$smarty.get.block}&fieldid={$smarty.get.fieldid}&mode=delete_buttons&formodule={$name}" data-mode="delete_buttons" data-formodule="{$name}"><img src="layouts/basic/modules/Settings/OSSPdf/link.png" class="create" alt="{vtranslate('DeleteButtons', 'OSSPdf')}" width="25" height="25" title="{vtranslate('DeleteButtons', 'OSSPdf')}"></a> 

							{/if}
							{* {if $created_buttons eq $name} <span style="color: orange;">{vtranslate('ButtonsAdded', 'OSSPdf')} </span>{/if}
							{if $deleted_buttons eq $name} <span style="color: orange;">{vtranslate('ButtonsDeleted', 'OSSPdf')} </span>{/if} *}
						</td>
					</tr>	
					<tr>
						<td colspan="2" height="3px">  </td>
					</tr>
				{/foreach}
			</table>
			<hr />	
			<form id="config_form" method="POST" action="index.php?module=OSSPdf&view=Index&parent=Settings&block={$smarty.get.block}&fieldid={$smarty.get.fieldid}&mode=update">
				<table width="650px">
					<tr>
						<td width="450px">{vtranslate('IfSave', 'OSSPdf')}</td>
						<td width="200px"><input type="checkbox" name="ifsave" {if $ifsave eq 'yes'} CHECKED {/if}></td>
					</tr>
					<tr>
						<td width="450px">{vtranslate('IfAttach', 'OSSPdf')}</td>
						<td width="200px"><input type="checkbox" name="ifattach" {if $ifattach eq 'yes'} CHECKED {/if}></td>
					</tr>
				</table>
				<br />
				<input id="acceptbutton" type="submit" onmouseover = "pointat();" onmouseout = "pointout();" style="border-color: darkgreen;border-style: solid;border-width: 1px 1px 1px 1px;width:175px;height: 34px;text-align: center;" value="{vtranslate('accept', 'OSSPdf')}">
			</form>
			</td>
			</tr>
			</table>
			<br>
			<form method="POST" action="javascript:void(0);">
				<div id="editdiv" class="fixedlay" style="display:none;position:absolute;width:450px;"></div>
			</form>            
		</div>
		<div class="tab-pane" id="function">
			{if $IS_ADMIN eq 'true'}
				<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
					<tr>
						<td class="showPanelBg" style="padding: 10px;" valign="top" width="100%">
							<br>
							<div align=center>
							</div>
							<input type="hidden" id="number_of_functions" name="number_of_functions" value="{$counter}" />
							<br/>
							<table width="100%"  cellspacing="0" cellpadding="0">
								<tr width="100%" height="100%">
									<td width="20%">
										<table width="100%" cellspacing="0" cellpadding="0">
											<tr height="20px"><td></td></tr>
													{foreach from=$functionlist key=functionname item=functions}
												<tr height="20px" width="100%"><td id="subtab{$functions.id}" onclick="chooseSubTab('{$functions.id}');" {if $functions.selected eq 'true' } class="dvtSelectedCell" {else} class="dvtUnSelectedCell" {/if}>{$functions.functionname}</td></tr>
												<tr height="10px"><td></td></tr>
													{/foreach}
										</table>
									</td>
									<td width="80%" height="100%" valign="top">
										<table border="0" cellspacing="0" cellpadding="3" height="100%" width="100%"  class="dvtContentSpace" style="border:1px solid #DEDEDE;" bgcolor="white">
											<tr width="100%" height="100%">
												<td>
													{foreach from=$functionlist key=functionname item=functions}
														<div id="subtab_fields{$functions.id}" name="{$functionname}" style="margin-top: 15px; margin-left: 10px;{if $functions.selected eq 'true' } {else} display:none; {/if} ">
															<div style="border: 1px solid #DEDEDE; padding: 4px; margin-bottom: 12px;"> <strong> {vtranslate('LBL_Variables_list', 'OSSPdf')} {vtranslate(substr($functionname, 0, -4), 'OSSPdf')}</strong></div>
															<form name="{$functionname}_variables" method="POST" action="index.php?module=OSSPdf&view=SaveVar&parent=Settings&block={$smarty.get.block}&fieldid={$smarty.get.fieldid}">
																<input type="hidden" name="fname" value="{$functionname}" /> 
																<div height="20px" width="150px" class="dvtCellInfo">
																	<table border="0" width="100%">
																		{foreach from=$functions.variables key=name item=variable}
																			<tr heigth="25px"><td width="40%">{$variable.label}</td>
																				<td width="60%">
																					<select name="{$name}" class="chzn-select form-control">
																						<option value="TRUE" {if $variable.value eq 'TRUE'} SELECTED {/if}>{vtranslate('LBL_TRUE', 'OSSPdf')}</option>
																						<option value="FALSE" {if $variable.value eq 'FALSE'} SELECTED {/if}>{vtranslate('LBL_FALSE', 'OSSPdf')}</option>
																					</select>
																				</td>
																			</tr>
																		{/foreach}
																	</table>
																</div>
																<button type="submit" class="btn btn-success pull-right pushDown">{vtranslate('Save', 'OSSPdf')}</button>
															</form>
															<br />
														</div>
													{/foreach}
												</td>
											</tr>
											<tr height="40px"><td> </td></tr>
										</table>
									</td>
								</tr>
							</table>
							<hr />	
						</td>    
					</tr>
				</table>
				<br>
				<form method="POST" action="javascript:void(0);">
					<div id="editdiv" class="fixedlay" style="display:none;position:absolute;width:450px;"></div>
				</form>
			{else}
				<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'>
					<tr>
						<td align='center'>
							<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 100000020;'>
								<table border='0' cellpadding='5' cellspacing='0' width='98%'>
									<tr>
										<td rowspan='2' width='11%'><img src="layouts/basic/skins/images/denied.gif" ></td>
										<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>{vtranslate('LBL_PERMISSION_DENIED', 'Vtiger')}</span></td>
									</tr>
									<tr>
										<td class='small' align='right' nowrap='nowrap'>			   	
											<a href='javascript:window.history.back();'>{$APP.LBL_GO_BACK}</a><br>
										</td>
									</tr>
								</table> 
							</div>
						</td>
					</tr>
				</table>
			{/if}
		</div>
	</div>
</div>
