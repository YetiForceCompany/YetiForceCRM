/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

function PDFselectedRecords(module, category)
{
    var allselectedboxes = document.getElementById("allselectedboxes");
    var idstring = (allselectedboxes == null) ? '' : allselectedboxes.value;
    if (idstring != '')
        window.location.href = "index.php?module=OSSPdf&usingmodule=" + module + "&action=ExportPDFRecords&parenttab=" + category + "&idstring=" + idstring;
    else
        window.location.href = "index.php?module=OSSPdf&usingmodule=" + module + "&action=ExportPDFRecords&parenttab=" + category;
    return false;
}

function QuickGenerate(module, category, id)
{
    var url = "index.php?module=OSSPdf&action=OSSPdfAjax&file=CheckForTemplates&id=" + id + "&usingmodule=" + module;
    jQuery.noConflict();
    //wywolanie funkcji poprzez ajax

    var result = jQuery.ajax({
        url: url,
        async: false,
        dataType: "html"
    }).responseText;

    var result = result.replace("}", "");
    var result = result.replace("{", "");
    var wynik = result.split(",");

    var zmienna = wynik[1].replace('"', '');
    var wynik_koncowy = zmienna.split(":");

    if (wynik_koncowy[1].replace('"', '') == 'error')
    {
        var params = {
            title: app.vtranslate('JS_ERROR'),
            text: wynik_koncowy[4].replace('"', ''),
            animation: 'show'
        };

        Vtiger_Helper_Js.showPnotify(params);
    }
    else
    {
        var templates = wynik_koncowy[4].replace('"', '');
        window.location.href = "index.php?module=OSSPdf&action=OSSPdfAjax&file=PDFExport&usingmodule=" + module + "&idstring=&recordid=" + id + "&pdfajax=true&fromdetailview=yes&template=" + templates + "&export_data=all&ParticularSave=&parenttab=" + category;
    }

    return false;
}


function QuickGenerateMail(module, category, id)
{
    var url = "index.php?module=OSSPdf&action=OSSPdfAjax&file=CheckForTemplates&id=" + id + "&usingmodule=" + module;
    jQuery.noConflict();
    //wywolanie funkcji poprzez ajax

    var result = jQuery.ajax({
        url: url,
        async: false
    }).responseText;


    var result = result.replace("}", "");
    var result = result.replace("{", "");
    var wynik = result.split(",");

    var zmienna = wynik[1].replace('"', '');
    var wynik_koncowy = zmienna.split(":");


    if (wynik_koncowy[1].replace('"', '') == 'error')
    {
        var params = {
            title: app.vtranslate('JS_ERROR'),
            text: wynik_koncowy[4].replace('"', ''),
            animation: 'show'
        };

        Vtiger_Helper_Js.showPnotify(params);
    }
    else
    {
        var templates = wynik_koncowy[4].replace('"', '');
        var url = "index.php?module=OSSPdf&action=OSSPdfAjax&file=PDFExport&usingmodule=" + module + "&idstring=&recordid=" + id + "&pdfajax=true&fromdetailview=yes&template=" + templates + "&export_data=all&ParticularSave=&return_name=yes&parenttab=" + category;

        var result = jQuery.ajax({
            url: url,
            async: false
        }).responseText;

        url = 'index.php?module=Emails&action=EmailsAjax&file=EditView&attachment=' + result;
        openPopUp('xComposeEmail', this, url, 'createemailWin', 820, 689, 'menubar=no,toolbar=no,location=no,status=no,resizable=no,scrollbars=yes');


        return false;
    }
}

function validate_templates(label, category, form, idstring)
{
    var template = document.getElementsByName("template[]");

    var licznik = 0;
    for (i = 0; i < template.length; i++)
    {
        if (template.item(i).checked == true)
        {
            licznik++;
        }
    }

    if (licznik > 0)
    {
        record_export(label, category, form, idstring);
    }
    else
    {
        var params = {
            title: app.vtranslate('JS_ERROR'),
            text: document.getElementsByName('warning')[0].value,
            animation: 'show'
        };

        Vtiger_Helper_Js.showPnotify(params);
    }
}

function validate_templates_mail(label, category, form, idstring)
{
    var template = document.getElementsByName("template[]");
    var licznik = 0;
    for (i = 0; i < template.length; i++)
    {
        if (template.item(i).checked == true)
        {
            licznik++;
        }
    }

    if (licznik > 0)
    {
        sendpdf_submit();
    }
    else
    {
        var params = {
            title: app.vtranslate('JS_ERROR'),
            text: document.getElementsByName('warning')[0].value,
            animation: 'show'
        };

        Vtiger_Helper_Js.showPnotify(params);
    }
}

function sendpdf_submit()
{
    var usingmodule = document.getElementsByName("usingmodule");
    var format = document.getElementsByName("osspdf_pdf_format");
    var recordid = document.getElementsByName("recordid");
    var fromdetailview = document.getElementsByName("fromdetailview");
    var export_data = document.getElementsByName("export_data");
    var idstring = document.getElementsByName("idstring");
    var id_cur_str = document.getElementsByName("id_cur_str");
    var ParticularSave = document.getElementsByName("ParticularSave");

    var idstring_val = idstring.item(0).value;
    var id_cur_str_val = id_cur_str.item(0).value;
    var usingmodule_val = usingmodule.item(0).value;
    var recordid_val = recordid.item(0).value;
    var fromdetailview_val = fromdetailview.item(0).value;
    if (fromdetailview_val == 'yes')
    {
        var field = document.getElementsByName("template[]");

        template_val = '';
        var ParticularSave_val = '';

        for (i = 0; i < field.length; i++)
        {
            if (field[ i ].checked == true)
            {
                if (i == (field.length - 1))
                {
                    template_val = template_val + field[ i ].value;
                }
                else
                {
                    template_val = template_val + field [ i ].value + ';';
                }
            }
        }
    }
    else
    {
        var template = document.getElementsByName("template");
        var ParticularSave_val = ParticularSave.item(0).options[ ParticularSave.item(0).selectedIndex ].value;
        var template_val = template.item(0).options[ template.item(0).selectedIndex ].value;
    }
    export_data_val = '';
    var url = 'index.php?module=OSSPdf&action=OSSPdfAjax&file=PDFExport&usingmodule=' + usingmodule_val + '&idstring=' + idstring_val + '&id_cur_str=' + id_cur_str_val + '&return_name=yes&recordid=' + recordid_val + '&fromdetailview=' + fromdetailview_val + '&template=' + template_val + '&export_data=' + export_data_val + '&ParticularSave=' + ParticularSave_val;

    var result = jQuery.ajax({
        url: url,
        async: false
    }).responseText;


    window.open('index.php?module=Emails&view=ComposeEmail&attachment=' + result + '&selected_ids=[' + recordid_val + ']', 'OSSPdf', 'width=600,height=500');

}

function check_params()
{
    var listInstance = Vtiger_List_Js.getInstance();
    var selectedIds = listInstance.readSelectedIds(true);
    var selectedIds = selectedIds.toString();
    if (selectedIds == "[]")
    {
        var params = {
            title: app.vtranslate('JS_ERROR'),
            text: document.getElementsByName('warning')[0].value,
            animation: 'show'
        };

        Vtiger_Helper_Js.showPnotify(params);
    }
    else
    {
        var selectedIds = selectedIds.replace("[", "");
        var selectedIds = selectedIds.replace("]", "");

        var selectedIds = selectedIds.split(",");
        for (i = 0; i < selectedIds.length; i++)
        {
            selectedIds[i] = selectedIds[i].replace('"', "");
            selectedIds[i] = selectedIds[i].replace('"', "");
        }
        var selectedIds = selectedIds.join(";");

        var idstring = document.getElementById("idstring");

        idstring.value = selectedIds;
        document.Export_Records.submit();
    }
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}