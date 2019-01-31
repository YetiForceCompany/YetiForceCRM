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
<div id="tpl-Settings-ApiAddress-Configuration menuEditorContainer">
    <div class="widget_header row mb-2">
        <div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
    </div>
	<div class="main_content">
		<form>
			<div class="col-12 form-row m-0">
				<div class="col-12 form-row">
					<h4>{\App\Language::translate('LBL_GLOBAL_CONFIG', $MODULENAME)} </h4>
				</div>
				<div class="col-12 form-row mb-2">
					<div class="col-sm-6 col-md-4">
						<div >
							{\App\Language::translate('LBL_MIN_LOOKUP_LENGTH', $MODULENAME)}: 
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="text-center">
							<input name="min_length" type="text" class="api form-control m-0" value="{$CONFIG['global']['min_length']}">
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-12 form-row  mb-2">
					<div class='col-sm-6 col-md-4'>
						<div>
							{\App\Language::translate('LBL_NUMBER_SEARCH_RESULTS', $MODULENAME)}: 
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="text-center">
							<input name="result_num" type="text" class="api form-control m-0" value="{$CONFIG['global']['result_num']}">
						</div>
					</div>
				</div>
				<div class="col-12 form-row mb-2">
					<div>
						<button type="button" class="btn btn-success saveGlobal"><span class="fa fa-check u-mr-5px"></span>{\App\Language::translate('LBL_SAVE_GLOBAL_SETTINGS', $MODULENAME)}</button>
					</div>
				</div>
				<div class="col-12">
					<hr>
				</div>
				<div class="col-12 form-row mb-2">
					<div class='col-md-4 col-sm-6'>
						{\App\Language::translate('LBL_CHOOSE_API', $MODULENAME)}
					</div>
					<div class='col-sm-6 col-md-4'>
						<select class="select2" id="change_api" class="form-control">
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
						<div class="apiContainer col-12 form-row px-3 pr-0 {if !$item["key"]}d-none{/if} api_row {$key}">
							{include file=\App\Layout::getTemplatePath($key|cat:'.tpl', $MODULENAME) API_INFO=$item API_NAME=$key}
						{/if}
					</div>
				{/foreach}
			</div>
		</form>	
	</div>
</div>
