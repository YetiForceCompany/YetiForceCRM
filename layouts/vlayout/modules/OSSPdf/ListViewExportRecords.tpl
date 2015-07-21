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

<!-- header - level 2 tabs -->
<!--onsubmit="VtigerJS_DialogBox.block();"--> 
<form id="Export_Records" name="Export_Records" style="margin: 10px;" role="form" method="POST" action="index.php">
        <input type="hidden" name="warning" value="{vtranslate('LBL_NORECORDS_CHECKED', 'OSSPdf')}" />
        <input type="hidden" name="module" value="OSSPdf">
        <input type="hidden" name="action" value="OSSPdfAjax">
        <input type="hidden" name="file" value="PDFExport">
        <input type="hidden" name="usingmodule" value="{$USINGMODULE}">
        <input type="hidden" id="idstring"  name="idstring" value="{$IDSTRING}">
        <input type="hidden" name="id_cur_str" value="{$IDCURSTR}">
        <input type="hidden" name="recordid" value="{$RECORD}">
        <input type="hidden" name="fromdetailview" value="{$FROM_DETAILVIEW}">
        <input type="hidden" name="only_generate" id="only_generate" value="0">
        {if $USINGMODULE eq 'Reports'}
            <input type="hidden" name="advft_criteria" value="{$advft_criteria}"/>
            <input type="hidden" name="advft_criteria_groups" value="{$advft_criteria_groups}"/>
        {/if}
        {if $NO_TEMPLATES eq 'yes'}
            <div class="form-group">
            <span class="genHeaderSmall" style="color: red; text-decoration: underline;">{vtranslate('LBL_NO_TEMPLATES', 'OSSPdf')}</span>
            </div>
        {else}
            <div class="form-group" style="margin-bottom: 8px;">
                <label for="template" style="text-decoration: underline;">{vtranslate('LBL_TEMPLATES', 'OSSPdf')}:</label>
            <select class="select-template form-control" id="template" name="template">
                {foreach item=template from=$templates}
                    <option value="{$template.id}">{$template.name}</option>
                {/foreach}
            </select>
            </div>
            <hr />
        {/if}
    {if $NO_TEMPLATES eq 'yes'}{else}
        <div class="form-group" style="margin-top: 8px;">
                <img src="layouts/vlayout/modules/OSSPdf/pdf.png" alt="{vtranslate('LBL_CREATE_PDF')}" onclick="jQuery('#only_generate').val('0'); check_params();" />
        {if $OSS_MILE_EXISTS}
                <img src="layouts/vlayout/modules/OSSPdf/email.png" alt="{vtranslate('LBL_CREATE_PDF_SEND_MAIL')}" onclick="jQuery('#only_generate').val('1'); check_params();" />
        {/if}
        </div>
    {/if}
</form>

{literal}
    <script>
        jQuery('select.select-template').select2();
    </script>
{/literal}
