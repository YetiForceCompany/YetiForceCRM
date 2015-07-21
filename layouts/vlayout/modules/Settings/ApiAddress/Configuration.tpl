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
<div class="" id="menuEditorContainer">
    <div class="widget_header row">
        <div class="col-md-8">
			<h3>{vtranslate('LBL_API_ADDRESS', $MODULENAME)}</h3>
			{vtranslate('LBL_API_ADDRESS_DESCRIPTION', $MODULENAME)}
		</div>
    </div>
    <hr>
	<div class="main_content" style="padding:30px">
		<form>
			<table cellpadding="10" data-api-name="global">
				<tr>
					<td>{vtranslate('LBL_GLOBAL_CONFIG', $MODULENAME)} </td>
				</tr>
				<tr>
					<td>
						<div style="max-width:250px;">
							{vtranslate('LBL_MIN_LOOKUP_LENGHT', $MODULENAME)}: 
						</div>
					</td>
					<td>
						<div style="text-align:center" >
							<input name="min_lenght" type="text" class="api form-control" value="{$CONFIG['global']['min_lenght']}" style="margin:0 auto;">
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div style="max-width:250px;">
							{vtranslate('LBL_NUMBER_SEARCH_RESULTS', $MODULENAME)}: 
						</div>
					</td>
					<td>
						<div style="text-align:center" >
							<input name="result_num" type="text" class="api form-control" value="{$CONFIG['global']['result_num']}" style="margin:0 auto;">
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<button type="button" class="btn btn-success save">{vtranslate('LBL_SAVE_GLOBAL_SETTINGS', $MODULENAME)}</button>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<hr />
					</td>
				</tr>
				<tr>
					<td>
						{vtranslate('LBL_CHOOSE_API', $MODULENAME)}
					</td>
					<td>
						<select class="select2" id="change_api" class="form-control" style="width: 200px;">
							<option>{vtranslate('LBL_SELECT_OPTION')}</option>
							{foreach from=$CONFIG item=item key=key}
								{if $key neq 'global'}
									<option value="{$key}">{vtranslate($key, $MODULENAME)}</option>
								{/if}

							{/foreach}
						</select>
					</td>
				</tr>
				{foreach from=$CONFIG item=item key=key}
					{if $key neq 'global'}
					<tr class="hide api_row {$key}">
						<td colspan="2" style="padding-top: 10px;">
							{include file=vtemplate_path($key|cat:'.tpl', $MODULENAME) API_INFO=$item API_NAME=$key}
						</td>
					{/if}
					</tr>
				{/foreach}

			</table> 
		</form>	
	</div>
</div>
