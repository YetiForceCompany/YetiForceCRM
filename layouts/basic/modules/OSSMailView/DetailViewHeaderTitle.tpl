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
	{assign var=IMAGE value=$MODULE_NAME|cat:'48.png'}
	{if file_exists( vimage_path($IMAGE) )}
		<span class="pull-left spanModuleIcon moduleIcon{$MODULE_NAME}">
			<span class="moduleIcon">
				<img src="{vimage_path($IMAGE)}" class="summaryImg" alt="{vtranslate($MODULE, $MODULE)}" />
			</span>
		</span>
	{/if}
    <span class="col-md-6 margin0px">
		<div class='row-1'>
            <h4 style="color: #1560bd;" title="{$RECORD->getName()}">
                {foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
                    {assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
                    {if $FIELD_MODEL->getPermissions()}
                        <span class="moduleColor_{$MODULE_NAME} {$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
                    {/if}
                {/foreach}
            </h4>
		</div>
		<div>
			<span class="muted">
				<small><em>{vtranslate('Sent','OSSMailView')}</em></small>
				<span><small><em>&nbsp;{$RECORD->get('createdtime')}</em></small></span>
			</span>
		</div>
		<div>
			<strong>{vtranslate('LBL_OWNER','Emails')} : {getOwnerName($RECORD->get('assigned_user_id'))}</strong>
		</div>
    </span>
{/strip}
