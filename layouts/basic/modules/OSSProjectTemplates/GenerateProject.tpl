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
<div class="widget_header row">
	<div class="col-md-12">
		{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
	</div>
</div>
<form class="form-horizontal recordEditView" id="generateProjectWidget" role="form" action="index.php" method="GET">
    <input type="hidden" name="rel_id" value="{$REL_ID}" />
    <input type="hidden" name="action" value="GenerateFromWidgetInProject" />
    <input type="hidden" name="module" value="{$MODULE_NAME}" />
    <table class="table table-bordered">
		{if !empty($TPL_LIST)}
			<tr>
				<td>
					{vtranslate('TPL_LIST', $MODULE_NAME)}</td>
			</tr>
			<tr>
				<td>
					<select name="id_tpl" class="select2">
						{foreach from=$TPL_LIST item=item key=key}
							<option value="{$key}">{$item.tpl_name}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<button class="btn btn-primary" id="generateFromTpl">{vtranslate('GENERATE', $MODULE_NAME)}</button>
				</td>
			</tr>
        {else}
            <tr><td>{vtranslate('NO_TPL', $MODULE_NAME)}</td></tr>
        {/if}
</form>
