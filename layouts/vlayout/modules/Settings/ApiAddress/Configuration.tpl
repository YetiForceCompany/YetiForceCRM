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
<div class="container-fluid" id="menuEditorContainer">
    <div class="widget_header row-fluid">
        <div class="span8">
			<h3>{vtranslate('LBL_API_ADDRESS', $MODULENAME)}</h3>
			{vtranslate('LBL_API_ADDRESS_DESCRIPTION', $MODULENAME)}
		</div>
    </div>
    <hr>
	<div class="main_content" style="padding:30px">
		<form>
			<table cellpadding="10">
				{if $CONFIG.key }
					<tr>
						<td>
							{vtranslate('LBL_USE_OPENCAGE_GEOCODER', $MODULENAME)}: 
						</td>
						<td>
							<div style="text-align:center">
								<input type="checkbox" name="nominatim" class="api" {if $CONFIG.nominatim } checked {/if}/>
							</div>
						</td>
					</tr>
					<tr>
						<td >
							<div style="max-width:250px;">
								{vtranslate('LBL_MIN_LOOKUP_LENGHT', $MODULENAME)}: 
							</div>
						</td>
						<td>
							<div style="text-align:center" >
								<input name="min_lenght" type="text" class="api" value="{$CONFIG.min_lenght}" style="width:20px; margin:0 auto;">
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<button type="button" class="btn btn-danger delete" id="delete">{vtranslate('LBL_REMOVE_CONNECTION', $MODULENAME)}</button>
						</td>
						<td>
							<button type="button" class="btn btn-success save" id="save" style="margin:0 auto;">{vtranslate('LBL_SAVE', $MODULENAME)}</button>
						</td>
					</tr>
				{else}
					<tr>
						<td>
							<div class="">
								<input name="key" type="text" class="api" style="margin:0 auto;" placeholder="{vtranslate('LBL_ENTER_KEY_APPLICATION', $MODULENAME)}">
							</div>
						</td>
						<td>
							<div>
								<a class="btn btn-primary" href="https://developer.opencagedata.com/" target="_blank">{vtranslate('OpenCage Geocoder', $MODULENAME)}</a>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<button type="button" class="btn btn-success save" id="save" style="margin:0 auto;">{vtranslate('LBL_SAVE', $MODULENAME)}</button>
						</td>
					</tr>
				{/if}
			</table> 
		</form>	
	</div>
</div>