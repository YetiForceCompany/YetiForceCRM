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
{strip}
<div class="" id="sharingAccessContainer">
	<div class="contents">
		<form name="EditSharingAccess" action="index.php" method="post" class="form-horizontal" id="EditSharingAccess">
			<input type="hidden" name="module" value="SharingAccess" />
			<input type="hidden" name="action" value="SaveAjax" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="dependentModules" value='{\App\Json::encode($DEPENDENT_MODULES)}' />
			
				<div class="widget_header row">
					<div class="col-md-8">
					    {include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
					</div>
					<div class="col-md-4">
						<button class="btn btn-success pull-right hide" type="submit" name="saveButton"><strong>{vtranslate('LBL_APPLY_NEW_SHARING_RULES', $QUALIFIED_MODULE)}</strong></button>
					</div>
				</div>
				<table class="table tableRWD table-bordered table-condensed sharingAccessDetails">
					<colgroup>
						<col width="20%">
						<col width="15%">
						<col width="15%">
						<col width="20%">
						<col width="10%">
						<col width="20%">
					</colgroup>
					<thead>
						<tr class="blockHeader">
							<th>
								{vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}
							</th>

							{foreach from=$ALL_ACTIONS key=ACTION_ID item=ACTION_MODEL}
								<th>
									{$ACTION_MODEL->getName()|vtranslate:$QUALIFIED_MODULE}
								</th>
							{/foreach}
							<th nowrap="nowrap">{'LBL_ADVANCED_SHARING_RULES'|vtranslate:$QUALIFIED_MODULE}</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
						<tr data-module-name="{$MODULE_MODEL->get('name')}">
							<td>
								{if $MODULE_MODEL->getName() == 'Accounts'}
									{$MODULE_MODEL->get('label')|vtranslate:$QUALIFIED_MODULE}
								{else}
									{$MODULE_MODEL->get('label')|vtranslate:$MODULE_MODEL->getName()}
								{/if}
							</td>
							{foreach from=$ALL_ACTIONS key=ACTION_ID item=ACTION_MODEL}
							<td class="">
								{if $ACTION_MODEL->isModuleEnabled($MODULE_MODEL)}
									<div><input type="radio" name="permissions[{$TABID}]" data-action-state="{$ACTION_MODEL->getName()}" title="{$ACTION_MODEL->getName()|vtranslate:$QUALIFIED_MODULE}" value="{$ACTION_ID}"{if $MODULE_MODEL->getPermissionValue() eq $ACTION_ID}checked="true"{/if}></div>
								{/if}
							</td>
							{/foreach}
							<td class="triggerCustomSharingAccess">
								<div class="row">
									<div class="col-md-3">&nbsp;</div>
									<div class="col-md-6">
										<button type="button" class="btn btn-xs btn-default vtButton arrowDown row" data-handlerfor="fields" data-togglehandler="{$TABID}-rules"><img src="{vimage_path('Arrow-down.png')}" alt=""></img></button>
										<button type="button" class="btn btn-xs btn-default vtButton arrowUp row hide" data-handlerfor="fields" data-togglehandler="{$TABID}-rules"><img src="{vimage_path('Arrow-up.png')}" alt=""></img></button>
									</div>
								</div>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			<div>
				<div class="pull-right">
					<button class="btn btn-success hide" type="submit" name="saveButton"><strong>{vtranslate('LBL_APPLY_NEW_SHARING_RULES', $QUALIFIED_MODULE)}</strong></button>
				</div>
			</div>
		</form>
	</div>
</div>
{/strip}
