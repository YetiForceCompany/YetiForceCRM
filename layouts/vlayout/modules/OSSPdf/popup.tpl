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
{if $ChosenModule neq '25'}
<tr>
	<td class="fieldLabel wideWidthType">
		<label class="muted pull-right marginRight10px">{$LBL_RELATED_MODULE}</label>
	</td>
	<td class="fieldValue wideWidthType">
		<select id='relatedmodule' title="{$LBL_RELATED_MODULE}" onchange="newvalues();">
			{foreach item=label key=name from=$RELMODULE}
				<option value="{$name}">{$label}</option>
			{/foreach}
		</select>
	</td>

	<td class="fieldLabel wideWidthType"><label class="muted pull-right marginRight10px">{$LBL_RELATED_FIELDS}</label></td>
	<td class="fieldValue wideWidthType">
		<select id='select_relatedfield' title="{$LBL_RELATED_FIELDS}" style="width: 200px;">	
			{foreach key=name item=single_field from=$RELATEDFIELDS}
				<optgroup label="{$name}">
				{foreach item=field from=$single_field}
					<option value="{$field.name}">{$field.label}</option>
				{/foreach}
				</optgroup>
			{/foreach}
		</select>
		<input type="hidden" value="" id="3" /><button class="btn btn-info pull-right marginRight10px" data-clipboard-target="3" id="copy3" title="{vtranslate('Field', 'OSSPdf')}"><span class="icon-download-alt"></span> </button>&nbsp;
		<input type="hidden" value="" id="4" /><button class="btn btn-warning pull-right marginRight10px" data-clipboard-target="4" id="copy4"  title="{vtranslate('Label', 'OSSPdf')}"><span class="icon-download-alt"></span> </button>
	</td>
</tr>
{/if}
{if $ProductModule eq 'yes'}
<tr>
	<td class="fieldLabel wideWidthType">
		<label class="muted pull-right marginRight10px">{$LBL_PRODUCT_MODULE}</label>
	</td>
	<td class="fieldValue wideWidthType">
		<select title="{$LBL_PRODUCT_MODULE}" id='productmodule'>
			{foreach item=label key=name from=$PRODMODULE}
				<option value="{$name}">{$label}</option>
			{/foreach}
		</select>
		<input type="hidden" value="" id="5" />
		<button class="btn btn-info pull-right marginRight10px" data-clipboard-target="5" id="copy5" title="{vtranslate('Field', 'OSSPdf')}"><span class="icon-download-alt"></span> </button>
	</td>
{else}
<tr>
{/if}
	<td class="fieldLabel wideWidthType"><label class="muted pull-right marginRight10px">{vtranslate({$LBL_COMPANY_DETAILS}, 'OSSPdf')}</label></td>
	<td class="fieldValue wideWidthType">
		<select id='companydata' title="{vtranslate({$LBL_COMPANY_DETAILS}, 'OSSPdf')}" style="width: 200px;">
			{foreach item=label key=name from=$COMPANY}
				<option value="{$name}">{$label}</option>
			{/foreach}
		</select>
		<input type="hidden" value="" id="6" /><button class="btn btn-info pull-right marginRight10px" data-clipboard-target="6" id="copy6" title="{vtranslate('Field','OSSPdf')}"><span class="icon-download-alt"></span> </button>
	</td>
</tr>
{if $ChosenModule neq '25'}
<tr>
	<td class="fieldLabel wideWidthType">
		<label class="muted pull-right marginRight10px">{$LBL_INSERTREPORT}</label>
	</td>
	<td class="fieldValue wideWidthType">
			<select id='reportid' title="{$LBL_INSERTREPORT}" class="enter_in">
					{foreach item=label key=name from=$REPORTS}
				<option value="{$name}">{$label}</option>
				{/foreach}
			</select><br/>
		<label style="margin: 0px 10px; float: left; color: #999999; display: block; font-size: 13px; font-weight: normal; line-height: 18px;">{$LBL_CHOSENMODULE}</label>
		<input type="checkbox" id="ifchosen" title="{$LBL_CHOSENMODULE}" name="ifchosen" />
		<input type="hidden" value="" id="7" />
		<button class="btn btn-info pull-right marginRight10px" data-clipboard-target="7" id="copy7" title="{vtranslate('Field', 'OSSPdf')}"><span class="icon-download-alt"></span> </button>
	</td>
{else}
<tr>
{/if}
	<td class="fieldLabel wideWidthType"><label class="muted pull-right marginRight10px">{$LBL_SET_DEFAULT_TEMPLATE}</label></td>
	<td class="fieldValue wideWidthType">
			<select id="templates" title="{$LBL_SET_DEFAULT_TEMPLATE}"  style="width: 200px;">
				<option name="start">{$LBL_SET_DEFAULT_TEMPLATE}</option>
				{foreach key=name item=value from=$TEMPLATES}
				<option name="{$name}">{$value}</option>
				{/foreach}
			</select>
	</td>
</tr>

<script>
$(document).ready( function() {

	
	copy_normalfield();
	copy_normallabel();
	copy_relatedfield();
	copy_relatedlabel();
	copy_specialfield();
	copy_companydata();
	copy_reportid();

	

jQuery('#select_default_field').change(function(){
copy_normallabel();
copy_normalfield();
});	

jQuery('#relatedmodule').change(function(){
copy_relatedlabel();
copy_relatedfield();
});	
jQuery('#select_relatedfield').change(function(){
copy_relatedlabel();
copy_relatedfield();
});	
jQuery('#productmodule').change(function(){
copy_specialfield();
});	
jQuery('#companydata').change(function(){
copy_companydata();
});	
jQuery('#reportid').change(function(){
copy_reportid();
});
jQuery('#ifchosen').change(function(){
copy_reportid();
});





var clip3 = new ZeroClipboard( 
    $('#copy3'), {
    moviePath: 'libraries/jquery/ZeroClipboard/ZeroClipboard.swf'
});
    
clip3.on( 'complete', function(client, args) {
    // notification about copy to clipboard
    var params = {
        text: app.vtranslate('LBL_NotifPassCopied'),
        animation: 'show',
        title: app.vtranslate('LBL_NotifPassTitle'),
        type: 'success'
    };
    Vtiger_Helper_Js.showPnotify(params);
} );
var clip4 = new ZeroClipboard( 
    $('#copy4'), {
    moviePath: 'libraries/jquery/ZeroClipboard/ZeroClipboard.swf'
});
    
clip4.on( 'complete', function(client, args) {
    // notification about copy to clipboard
    var params = {
        text: app.vtranslate('LBL_NotifPassCopied'),
        animation: 'show',
        title: app.vtranslate('LBL_NotifPassTitle'),
        type: 'success'
    };
    Vtiger_Helper_Js.showPnotify(params);
} );

var clip5 = new ZeroClipboard( 
    $('#copy5'), {
    moviePath: 'libraries/jquery/ZeroClipboard/ZeroClipboard.swf'
});
    
clip5.on( 'complete', function(client, args) {
    // notification about copy to clipboard
    var params = {
        text: app.vtranslate('LBL_NotifPassCopied'),
        animation: 'show',
        title: app.vtranslate('LBL_NotifPassTitle'),
        type: 'success'
    };
    Vtiger_Helper_Js.showPnotify(params);
} );
var clip6 = new ZeroClipboard( 
    $('#copy6'), {
    moviePath: 'libraries/jquery/ZeroClipboard/ZeroClipboard.swf'
});
    
clip6.on( 'complete', function(client, args) {
    // notification about copy to clipboard
    var params = {
        text: app.vtranslate('LBL_NotifPassCopied'),
        animation: 'show',
        title: app.vtranslate('LBL_NotifPassTitle'),
        type: 'success'
    };
    Vtiger_Helper_Js.showPnotify(params);
} );

var clip7 = new ZeroClipboard( 
    $('#copy7'), {
    moviePath: 'libraries/jquery/ZeroClipboard/ZeroClipboard.swf'
});
    
clip7.on( 'complete', function(client, args) {
    // notification about copy to clipboard
    var params = {
        text: app.vtranslate('LBL_NotifPassCopied'),
        animation: 'show',
        title: app.vtranslate('LBL_NotifPassTitle'),
        type: 'success'
    };
    Vtiger_Helper_Js.showPnotify(params);
} );
	


var clip1 = new ZeroClipboard( 
    $('#copy-1'), {
    moviePath: 'libraries/jquery/ZeroClipboard/ZeroClipboard.swf'
});
    
clip1.on( 'complete', function(client, args) {
    // notification about copy to clipboard
    var params = {
        text: app.vtranslate('LBL_NotifPassCopied'),
        animation: 'show',
        title: app.vtranslate('LBL_NotifPassTitle'),
        type: 'success'
    };
    Vtiger_Helper_Js.showPnotify(params);
} );
var clip2 = new ZeroClipboard( 
    $('#copy-2'), {
    moviePath: 'libraries/jquery/ZeroClipboard/ZeroClipboard.swf'
});
    
clip2.on( 'complete', function(client, args) {
    // notification about copy to clipboard
    var params = {
        text: app.vtranslate('LBL_NotifPassCopied'),
        animation: 'show',
        title: app.vtranslate('LBL_NotifPassTitle'),
        type: 'success'
    };
    Vtiger_Helper_Js.showPnotify(params);
} );







} );
</script>

