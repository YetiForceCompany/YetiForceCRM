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
<div class="modelContainer modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header contentsBackground">
				<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
				<h3 class="modal-title">{vtranslate('GENERATE_FROM_TPL', $MODULE_NAME)}</h3>
			</div>
			<form class="form-horizontal recordEditView" role="form" action="index.php" method="GET">
				<input type="hidden" name="rel_id" value="{$REL_ID}" />
				<input type="hidden" name="action" value="Generate" />
				<input type="hidden" name="module" value="{$MODULE_NAME}" />

				<div class="modal-body">
					<table class="massEditTable table table-bordered">
						{if !empty($TPL_LIST)}
						<tr>
							<td>
								{vtranslate('TPL_LIST', $MODULE_NAME)}</td>
							<td>
								<select name="id_tpl" class="form-control">
									{foreach from=$TPL_LIST item=item key=key}
										<option value="{$key}">{$item.tpl_name}</option>
									{/foreach}
								</select>
							</td>
							<td>
								<button class="btn btn-primary" id="generateFromTpl">{vtranslate('GENERATE', $MODULE_NAME)}</button>
							</td>
						</tr>
						{else}
							{vtranslate('NO_TPL', $MODULE_NAME)}
						{/if}
				</div>
			</form>
		</div>
	</div>
</div>
