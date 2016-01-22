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
<div class="targetFieldsTableContainer">
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate('LBL_HIDEBLOCKS_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
	<form method="post" action="index.php?module={$MODULE}&parent=Settings&view=Conditions">
	<input type="hidden" name="record" value="{$RECORD_ID}"/>
	<div class="listViewEntriesDiv contents-bottomscroll" style="overflow-x: visible !important;">
		<div class="bottomscroll-div table-responsive">
			<table class="table table-bordered" width="100%" name="targetModuleFields">
				<tr class="blockHeader">
					<th class="blockHeader" colspan="2">
						{if $MODE}
							{vtranslate('LBL_EDIT_BLOCK', $QUALIFIED_MODULE)}
						{else}
							{vtranslate('LBL_NEW_BLOCK', $QUALIFIED_MODULE)}
						{/if}
					</th>
				</tr>
				<tr name="fieldHeaders">
					<td><b>{vtranslate('LBL_BLOCK', $QUALIFIED_MODULE)}</b></td>
					<td>
						<div class="col-md-5">
							<select class="chzn-select form-control" name="blockid">
								{foreach from=$BLOCKS item=MODULES key=key}
									<optgroup label="{vtranslate($key, $key)}">
									{foreach from=$MODULES item=item key=key}
										<option value="{$key}" {if $BLOCK_ID == $key}selected=""{/if}>{vtranslate($item['blocklabel'],$item['module'])}</option>
									{/foreach}
									</optgroup>
								{/foreach}
							</select>
						</div>
					</td>
				</tr>
				<tr name="fieldHeaders">
					<td><b>{vtranslate('LBL_ENABLED', $QUALIFIED_MODULE)}</b></td>
					<td>
						<div class="col-md-5">
							<input type="checkbox" name="enabled" value="true" {if $ENABLED}checked{/if} />
						</div>
					</td>
				</tr>
				<tr name="fieldHeaders">
					<td><b>{vtranslate('LBL_VIEW', $QUALIFIED_MODULE)}</b></td>
					<td class="col-md-10">
						<div class="col-md-5">
							<select multiple class="chzn-select form-control" name="views[]">
								{foreach from=$VIEWS item=LABEL key=VIEW_NAME}
									<option value="{$VIEW_NAME}" {if in_array($VIEW_NAME,$SELECTED_VIEWS)}selected=""{/if}>{vtranslate($LABEL,$QUALIFIED_MODULE)}</option>
								{/foreach}
							</select>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<br>	
	<div class="">
		<div class="pull-right">
			<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>
			<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
		</div>
		<div class="clearfix"></div>
	</div>
    </form>	
</div>
{/strip}
