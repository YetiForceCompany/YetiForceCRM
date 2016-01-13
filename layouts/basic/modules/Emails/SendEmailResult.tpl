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
{if $SUCCESS}
<script language="Javascript" type="text/javascript">
	window.close();
	{if $RELATED_LOAD eq true}
        window.opener.Vtiger_Detail_Js.reloadRelatedList();
	{else}
		window.opener.Vtiger_List_Js.clearList();
    {/if}
</script>
{else}
<table border='0' cellpadding='5' cellspacing='0' width='100%' height='600px'>
	<tr>
		<td align='center'>
			<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 75%; position: relative; z-index: 100000020;'>

				<table border='0' cellpadding='5' cellspacing='0' width='98%'>
					<tr>
						<td rowspan='2' width='11%'><img src="{vimage_path('denied.gif')}" ></td>
						<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'>
							<span class='genHeaderSmall'>{vtranslate($MESSAGE)}</span></td>
					</tr>
					<tr>
						<td class='small' align='right' nowrap='nowrap'>
							<a href='javascript:window.history.back();'>{vtranslate('LBL_GO_BACK')}</a><br>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>
{/if}