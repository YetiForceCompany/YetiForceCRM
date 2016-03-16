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
{* create template prompt *}
<div id="add_project_modal" class="modal fade" tabindex="-1">
	<div class="modal-dialog modal-lg">
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title">{vtranslate('FIELD_LIST', $MODULE_NAME)}</h3>
			</div>
			<form action="index.php" method="post" name="project_form">
				<div class="modal-body row no-margin">
					<input type="hidden" name='module' value="OSSProjectTemplates" />
					<input type="hidden" name='base_module' value="Project" />
					<input type="hidden" name='action' value="CreateTemplate" />
					<input type="hidden" name='parent' value="Settings" />
					<input type="hidden" name='back_view' value="Index" />
						<div class="col-md-12">
							<div class="col-md-5 fieldLabel"><span class="redColor">*</span> {vtranslate('LBL_TPL_NAME', $MODULE_NAME)}</div>
							<div class="col-md-7 paddingBottom10"><input class="required form-control input-sm" name="tpl_name" value="" type="text" /></div>
						{assign var=FIRST_ROW value=0}
						{assign var=COUNTER value=0}
						{foreach from=$FIELD_HTML key=key item=item}
								{if $COUNTER eq 2}
									</div><div class="col-md-12 paddingBottom10">
									{assign var=COUNTER value=1}
								{else}
									{assign var=COUNTER value=$COUNTER+1}
								{/if}
								{if $item.mandatory}
									<div class="col-md-5 fieldLabel"><span class="redColor">*</span> {vtranslate($item.label, 'Project')}</div>    
								{else}
									<div class="col-md-5 fieldLabel">{vtranslate($item.label, 'Project')}</div>
								{/if}
								<div class="col-md-7 elementGroup paddingBottom10">{$item.html}</div>
								{if $FIRST_ROW eq 0}
									</div>
									<div class="col-md-12">
									{assign var=COUNTER value=0}
									{assign var=FIRST_ROW value=$FIRST_ROW+1}
								{/if}
							
						{/foreach}
						</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success okay-button" >{vtranslate('Save', $MODULE_NAME)}</button>
					<a href="#" class="btn btn-warning" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
				</div>      
			</form>
		</div>
	</div>
</div>
