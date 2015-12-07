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
{literal}
    <style>
        #submitOSSMail {
            background-image:url('{/literal}{Yeti_Layout::getLayoutFile('modules/OSSPdf/email.png')}{literal}');
            background-repeat: no-repeat;
            background-position:  0px 0px;
            font-size:0;
            width:24px;
            height:24px;
            margin: 0px;
            padding: 0px;
        }
    </style>
{/literal}

<!-- header - level 2 tabs -->
<!--onsubmit="VtigerJS_DialogBox.block();"--> 
<form id="Export_Records" name="Export_Records" style="margin: 10px;" method="POST" action="index.php">
        <input type="hidden" name="warning" value="{vtranslate('LBL_NO_CHECKED_TEMPLATES', 'OSSPdf')}" />
        <input type="hidden" name="module" value="OSSPdf">
        <input type="hidden" name="action" value="OSSPdfAjax">
        <input type="hidden" name="file" value="PDFExport">
        <input type="hidden" name="usingmodule" value="{$USINGMODULE}">
        <input type="hidden" name="idstring" value="{$IDSTRING}">
        <input type="hidden" name="id_cur_str" value="{$IDCURSTR}">
        <input type="hidden" name="recordid" value="{$RECORD}">
        <input type="hidden" name="fromdetailview" value="{$FROM_DETAILVIEW}">
        <input type="hidden" name="only_generate" id="only_generate" value="0">

        {if $USINGMODULE eq 'Reports'}
            <input type="hidden" name="advft_criteria" value="{$advft_criteria}"/>
            <input type="hidden" name="advft_criteria_groups" value="{$advft_criteria_groups}"/>
        {/if}
        {if $NO_TEMPLATES eq 'yes'}
            <span class="genHeaderSmall" style="color: red; text-decoration: underline;">{vtranslate('LBL_NO_TEMPLATES', 'OSSPdf')}</span>
        {else}
            <span class="genHeaderSmall" style="text-decoration: underline;">{vtranslate('LBL_TEMPLATES', 'OSSPdf')}:</span>
            {if $FROM_DETAILVIEW eq 'yes'}

                {foreach item=template from=$templates}
                    <div class="checkbox">
						<label>
							<input id="tpl" type="checkbox" name="template[]" value="{$template.id}" title="{$template.name}" {if $template.checked eq 1} CHECKED {/if}/> {$template.name}
						</label>
					</div>
                {/foreach}
            {/if}
        {/if}
        <hr />
    {if $NO_TEMPLATES eq 'yes'}{else}
        <div class="form-group" style="margin-top: 8px;">
        <img name="{vtranslate('LBL_EXPORT')}" alt="{vtranslate('LBL_CREATE_PDF')}" src="{Yeti_Layout::getLayoutFile('modules/OSSPdf/pdf.png')}" onclick="
               {literal}
            jQuery('#only_generate').val('0');
            var toExport = false;
            jQuery('input[type=checkbox]').each(function() {
                if (this.id === 'tpl') {
                    if (jQuery(this).is(':checked')) {
                        toExport = true;
                    }
                }
            });

            if (toExport){
                Export_Records.submit();
            }
            else{
            var params = {
                title: app.vtranslate('JS_ERROR'),
                text: '{/literal}{vtranslate('NOT_SELECTED_TEMPLATE', 'OSSPdf')}{literal}',
                animation: 'show'
            };

            Vtiger_Helper_Js.showPnotify(params);
            }
               {/literal}
               " />         {if $OSS_MILE_EXISTS}
            <img src="{vimage_path('email.png')}" alt="{vtranslate('LBL_CREATE_PDF_SEND_MAIL')}" onclick="
                {literal}
                jQuery('#only_generate').val('1');
                
                var toExport = false;
                jQuery('input[type=checkbox]').each(function() {
                    if (this.id === 'tpl') {
                        if (jQuery(this).is(':checked')) {
                            toExport = true;
                        }
                    }
                });
                
                
                if (!toExport){
                var params = {
                    title: app.vtranslate('JS_ERROR'),
                    text: '{/literal}{vtranslate('NOT_SELECTED_TEMPLATE', 'OSSPdf')}{literal}',
                    animation: 'show'
                };

                Vtiger_Helper_Js.showPnotify(params);
                return false;
                }
                
                jQuery('#Export_Records').submit();
                {/literal}
            " />
        {/if}
        </div>
    {/if}
</form>

