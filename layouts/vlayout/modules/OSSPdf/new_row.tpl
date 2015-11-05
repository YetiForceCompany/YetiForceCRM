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
<select name="{$row}_field" style="width: 200px;">	
				{foreach key=name item=single_field from=$DEFAULT_FIELDS}
					{foreach item=field from=$single_field}
						<option value="{$field.name}">{vtranslate($field.label, 'OSSPdf')}</option>
					{/foreach}
				{/foreach}
			</select>
		
		
		<select name="{$row}_comparator">	
				{foreach key=name item=label from=$COMPARATORS}
                                    <option value="{$name}">{vtranslate($label, 'OSSPdf')} </option>
					
				{/foreach}
			</select>
		<input name="{$row}_fieldvalue" size="50" value="" />
	<span onClick="delete_row('{$row}');">	<img src="modules/com_vtiger_workflow/resources/remove.png" ></span>