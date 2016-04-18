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
<div id="menuEditorContainer">
    <div class="widget_header row">
        <div class="col-md-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate('LBL_API_ADDRESS_DESCRIPTION', $MODULENAME)}
		</div>
    </div>
    <hr>
	<div class="main_content">
		<form>
			<div class="col-xs-12 row">
				<div class="col-xs-12 row">
					<h4>{vtranslate('LBL_GLOBAL_CONFIG', $MODULENAME)} </h4>
				</div>
				<div class="col-xs-12 row marginBottom5px">
					<div class="col-sm-6 col-md-4 row">
						<div >
							{vtranslate('LBL_MIN_LOOKUP_LENGHT', $MODULENAME)}: 
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div style="text-align:center" >
							<input name="min_lenght" type="text" class="api form-control" value="{$CONFIG['global']['min_lenght']}" style="margin:0 auto;">
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-xs-12 row  marginBottom5px">
					<div class='col-sm-6 col-md-4 row'>
						<div>
							{vtranslate('LBL_NUMBER_SEARCH_RESULTS', $MODULENAME)}: 
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div style="text-align:center" >
							<input name="result_num" type="text" class="api form-control" value="{$CONFIG['global']['result_num']}" style="margin:0 auto;">
						</div>
					</div>
				</div>
				<div class="col-xs-12 row marginBottom5px">
					<div>
						<button type="button" class="btn btn-success saveGlobal">{vtranslate('LBL_SAVE_GLOBAL_SETTINGS', $MODULENAME)}</button>
					</div>
				</div>
				<div class="col-xs-12 row marginBottom5px">
					<hr>
				</div>
				<div class="col-xs-12 row marginBottom5px">
					<div class=' row col-md-4 col-sm-6'>
						{vtranslate('LBL_CHOOSE_API', $MODULENAME)}
					</div>
					<div class='col-sm-6 col-md-4'>
						<select class="select2" id="change_api" class="form-control" style="width: 200px;">
							<option>{vtranslate('LBL_SELECT_OPTION')}</option>
							{foreach from=$CONFIG item=item key=key}
								{if $key neq 'global'}
									<option value="{$key}">{vtranslate($key, $MODULENAME)}</option>
								{/if}

							{/foreach}
						</select>
					</div>
				</div>
				{foreach from=$CONFIG item=item key=key}
					{if $key neq 'global'}
						<div class="apiContainer col-xs-12 paddingLRZero {if !$item["key"]}hide{/if} api_row {$key}">
							{include file=vtemplate_path($key|cat:'.tpl', $MODULENAME) API_INFO=$item API_NAME=$key}
						{/if}
					</div>
				{/foreach}

			</div> 
		</form>	
	</div>
</div>
