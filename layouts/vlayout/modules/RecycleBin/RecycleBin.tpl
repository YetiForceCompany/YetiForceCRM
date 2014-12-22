{*<!--

/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

-->*}
<script language="JavaScript" type="text/javascript" src="include/js/search.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/ListView.js"></script>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
{include file='Buttons_List.tpl'}
                                <div id="searchingUI" style="display:none;">
                                        <table border=0 cellspacing=0 cellpadding=0 width=100%>
                                        <tr>
                                                <td align=center>
                                                <img src="{'searching.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_SEARCHING}"  title="{$APP.LBL_SEARCHING}">
                                                </td>
                                        </tr>
                                        </table>

                                </div>
                        </td>
                </tr>
                </table>
        </td>
</tr>
</table>

{*<!-- Contents -->*}

<table border=0  cellspacing=0 cellpadding=0 width=98% align=center>

<tr><td valign=top><img src="{'showPanelTopLeft.gif'|@vtiger_imageurl:$THEME}"></td>
	<td class="showPanelBg" valign="top" width=100% style="padding:10px;">

		<form name="basicSearch" action="index.php" onsubmit="return false;">
		<div id="searchAcc" style="display: block;position:relative;">
			<table width="80%" cellpadding="5" cellspacing="0"  class="searchUIBasic small" align="center" border=0>
				<tr>
					<td class="searchUIName small" nowrap align="left">
						<span class="moduleName">{$APP.LBL_SEARCH}</span><br>		
					</td>
					<td class="small" nowrap align=right><b>{$APP.LBL_SEARCH_FOR}</b></td>
					<td class="small"><input type="text"  class="txtBox" style="width:120px" name="search_text"></td>
					<td class="small" nowrap><b>{$APP.LBL_IN}</b>&nbsp;</td>
					<td class="small" nowrap>
						<div id="basicsearchcolumns_real">
							<select name="search_field" id="bas_searchfield" class="txtBox" style="width:150px">
							{html_options  options=$SEARCHLISTHEADER }
							</select>
						</div>
						<input type="hidden" name="searchtype" value="BasicSearch">
						<input type="hidden" name="module" value="{$SELECTED_MODULE}">
						<input type="hidden" name="parenttab" value="{$CATEGORY}">
						<input type="hidden" name="action" value="index">
						<input type="hidden" name="query" value="true">
						<input type="hidden" name="search_cnt">
					</td>
					<td class="small" nowrap>
						<input name="submit" type="button" class="crmbutton small create" onClick="callRBSearch('Basic');" value=" {$APP.LBL_SEARCH_NOW_BUTTON} ">&nbsp;					
					</td>
					<td class="small" valign="top" onMouseOver="this.style.cursor='pointer';" onclick="moveMe('searchAcc');searchshowhide('searchAcc','')">[x]</td>
				</tr>
				<tr>
					<td colspan="7" align="center" class="small">
						<table border=0 cellspacing=0 cellpadding=0 width=100%>
							<tr>
								{$ALPHABETICAL}
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		</form>

{*<!-- Searching UI -->*}

	  <div id="modules_datas" class="small" style="width:100%;">
			{include file="modules/$MODULE/RecycleBinContents.tpl"}
	</div>
</tr></td>


</div>
</td>
</tr>
</table>
</td>
</tr>
</table>

	</td>
        <td valign="top"><img src="{'showPanelTopRight.gif'|@vtiger_imageurl:$THEME}"></td>
   </tr>
</tbody>
</table>

<div style="display: none;" class="veil_new small" id="rb_empty_conf_id">
	<table cellspacing="0" cellpadding="18" border="0" class="options small">
	<tbody>
		<tr>
			<td nowrap="" align="center" style="color: rgb(255, 255, 255); font-size: 15px;">
				<b>{$MOD.MSG_EMPTY_RB_CONFIRMATION}</b>
			</td>
		</tr>
		<tr>
			<td align="center">
				<input type="button" onclick="return emptyRecyclebin('rb_empty_conf_id');" value="{$APP.LBL_YES}"/>  
				<input type="button" onclick="$('rb_empty_conf_id').style.display='none';" value="{$APP.LBL_NO}"/>
			</td>
		</tr>
	</tbody>
	</table>
</div>