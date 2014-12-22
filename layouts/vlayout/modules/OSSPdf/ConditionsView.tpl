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
<div class="well well-small">

<div class="header"><span><strong>{vtranslate('LBL_CONDITION_ALL', $MODULE)}</strong></span> - <span>{vtranslate('LBL_CONDITION_ALL_DSC', $MODULE)}</span></div>
<hr/>
{foreach from=$REQUIRED_CONDITIONS key=key item=item name=field_select}
	<div class="row-fluid conditionRow marginBottom10px" >
		<span class="span4">{vtranslate($item['info']['label'], $BASE_MODULE)}</span>
		<span class="span3">{Conditions::translateType($item['comparator'],$MODULE)}</span>
		<span class="span4">
			{if $item['info']['type'] == 'picklist' || $item['info']['type'] == 'multipicklist' }
				{vtranslate($item['val'], $BASE_MODULE)}
			{else}
				{$item['val']}
			{/if}
		</span>
	</div>
{/foreach}
<br/>
<div class="header"><span><strong>{vtranslate('LBL_CONDITION_OPTION', $MODULE)}</strong></span> - <span>{vtranslate('LBL_CONDITION_OPTION_DSC', $MODULE)}</span></div>
<hr/>
{foreach from=$OPTIONAL_CONDITIONS key=key item=item name=field_select}
	<div class="row-fluid conditionRow marginBottom10px" >
		<span class="span4">{vtranslate($item['info']['label'], $BASE_MODULE)}</span>
		<span class="span3">{Conditions::translateType($item['comparator'],$MODULE)}</span>
		<span class="span4">{$item['val']}</span>
	</div>
{/foreach}
</div>