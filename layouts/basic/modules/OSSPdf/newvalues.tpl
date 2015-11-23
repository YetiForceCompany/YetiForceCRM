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
			<select id='select_relatedfield' class="form-control">	
				{foreach key=name item=single_field from=$RELATEDFIELDS}
						<optgroup label="{vtranslate($name, 'OSSPdf')}">
					{foreach item=field from=$single_field}
						<option value="{$field.name}">{vtranslate($field.label, 'OSSPdf')}</option>
					{/foreach}
						</optgroup>
				{/foreach}
			</select>
	<script>
	copy_relatedfield();
	copy_relatedlabel();
	jQuery('#select_relatedfield').change(function(){
	copy_relatedlabel();
	copy_relatedfield();
	});	
	</script>
