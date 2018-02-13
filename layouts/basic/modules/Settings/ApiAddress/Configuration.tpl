{*<!--
/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
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
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			{\App\Language::translate('LBL_API_ADDRESS_DESCRIPTION', $MODULENAME)}
		</div>
    </div>
    <hr>
	<div class="main_content">
		<form>
			<div class="col-12 row">
				<div class="col-12 row">
					<h4>{\App\Language::translate('LBL_GLOBAL_CONFIG', $MODULENAME)} </h4>
				</div>
				<div class="col-12 row marginBottom5px">
					<div class="col-sm-6 col-md-4 row">
						<div >
							{\App\Language::translate('LBL_MIN_LOOKUP_LENGTH', $MODULENAME)}: 
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div style="text-align:center" >
							<input name="min_length" type="text" class="api form-control" value="{$CONFIG['global']['min_length']}" style="margin:0 auto;">
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-12 row  marginBottom5px">
					<div class='col-sm-6 col-md-4 row'>
						<div>
							{\App\Language::translate('LBL_NUMBER_SEARCH_RESULTS', $MODULENAME)}: 
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div style="text-align:center" >
							<input name="result_num" type="text" class="api form-control" value="{$CONFIG['global']['result_num']}" style="margin:0 auto;">
						</div>
					</div>
				</div>
				<div class="col-12 row marginBottom5px">
					<div>
						<button type="button" class="btn btn-success saveGlobal">{\App\Language::translate('LBL_SAVE_GLOBAL_SETTINGS', $MODULENAME)}</button>
					</div>
				</div>
				<div class="col-12 row marginBottom5px">
					<hr>
				</div>
				<div class="col-12 row marginBottom5px">
					<div class=' row col-md-4 col-sm-6'>
						{\App\Language::translate('LBL_CHOOSE_API', $MODULENAME)}
					</div>
					<div class='col-sm-6 col-md-4'>
						<select class="select2" id="change_api" class="form-control" style="width: 200px;">
							<option>{\App\Language::translate('LBL_SELECT_OPTION')}</option>
							{foreach from=$CONFIG item=item key=key}
								{if $key neq 'global'}
									<option value="{$key}">{\App\Language::translate($key, $MODULENAME)}</option>
								{/if}

							{/foreach}
						</select>
					</div>
				</div>
				{foreach from=$CONFIG item=item key=key}
					{if $key neq 'global'}
						<div class="apiContainer col-12 paddingLRZero {if !$item["key"]}hide{/if} api_row {$key}">
							{include file=\App\Layout::getTemplatePath($key|cat:'.tpl', $MODULENAME) API_INFO=$item API_NAME=$key}
						{/if}
					</div>
				{/foreach}

			</div> 
		</form>	
	</div>
</div>
