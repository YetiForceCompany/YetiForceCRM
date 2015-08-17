/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

function tips() {
	$('.cloudlet').tooltipsy({
		css: {
			'padding': '10px',
			'max-width': '300px',
			'color': '#303030',
			'background-color': '#f5f5b5',
			'border': '1px solid #deca7e',
			'-moz-box-shadow': '0 0 10px rgba(0, 0, 0, .5)',
			'-webkit-box-shadow': '0 0 10px rgba(0, 0, 0, .5)',
			'box-shadow': '0 0 10px rgba(0, 0, 0, .5)',
			'text-shadow': 'none'
		}
	});
}
function PDFselectedRecords(module, category)
{
	var allselectedboxes = document.getElementById("allselectedboxes");
	var idstring = (allselectedboxes == null) ? '' : allselectedboxes.value;
	if (idstring != '')
		window.location.href = "index.php?module=OSSPdf&usingmodule=" + module + "&action=ExportRecords&parenttab=" + category + "&idstring=" + idstring;
	else
		window.location.href = "index.php?module=OSSPdf&usingmodule=" + module + "&action=ExportRecords&parenttab=" + category;
	return false;
}

function include(destination) {
	var e = window.document.createElement('script');
	e.setAttribute('src', destination);
	window.document.body.appendChild(e);
}
//include( "include/jquery/jquery-1.6.2.min.js" );
//include( "include/ckeditor/ckeditor.js" );

////////////////////////////////////////
function test()
{
	var obiekt = document.getElementsByName('moduleid');
	var nr = obiekt[0].selectedIndex;
	var txt = obiekt[0].options[ nr ].value;// document.getElementById('moduleid').options[document.getElementById('moduleid').selectedIndex].value;
	$.get("index.php?module=OSSPdf&action=OSSPdfAjax&mode=Popup&changedindex=true&moduleid=" + txt + "&selected_module=" + txt, function (data) {
		var elements = $('[id="div_2"]');
		elements.each(function () {
			$(this).parent().html(data.result);
			//$("#templatecontent").val( $(this).val() );
		});
		//
	});
	//CKEDITOR.replace( 'templatecontent' );
}
////////////////////////////////////////
function newvalues()
{
	var obiekt = document.getElementById('relatedmodule');
	var nr = obiekt.selectedIndex;

	var txt = obiekt.options[ nr ].value;// document.getElementById('moduleid').options[document.getElementById('moduleid').selectedIndex].value;

	$.get("index.php?module=OSSPdf&action=OSSPdfAjax&mode=Popup&changerelatedmodule=true&relatedmoduleid=" + txt, function (data) {
		var elements = $('[id="select_relatedfield"]');
		elements.each(function () {
			$(this).parent().html(data.result);
			//$("#templatecontent").val( $(this).val() );
		});
		//
	});
}
////////////////////////////////////////
function copy_normalfield()
{
	var wartosc = $('[id="select_default_field"]').val();
	$('[id="id1"]').val("#" + wartosc + "#");
}
////////////////////////////////////////
function copy_relatedfield()
{
	var wartosc = $('[id="select_relatedfield"]').val();
	var module = $('[id="relatedmodule"]').val();
	$('[id="3"]').val("#" + module + "_" + wartosc + "#");
}
////////////////////////////////////////
////////////////////////////////////////
function copy_normallabel()
{
	var wartosc = $('[id="select_default_field"]').val();
	$('[id="id2"]').val("#label_" + wartosc + "#");
}
////////////////////////////////////////
function copy_relatedlabel()
{
	var wartosc = $('[id="select_relatedfield"]').val();
	var module = $('[id="relatedmodule"]').val();
	$('[id="4"]').val("#" + module + "_label_" + wartosc + "#");
}
////////////////////////////////////////
////////////////////////////////////////
function copy_specialfield()
{
	var wartosc = $('[id="productmodule"]').val();
	$('[id="5"]').val(wartosc);
}
////////////////////////////////////////
function copy_companydata()
{
	var wartosc = $('[id="companydata"]').val();


	var company = document.getElementById("companydata");
//var ParticularSave_val = ParticularSave.item(0).options[ ParticularSave.item(0).selectedIndex ].value;
	if (company.selectedIndex == 1)
	{
		$('[id="6"]').val(wartosc);
	}
	else
	{
		$('[id="6"]').val("#" + wartosc + "#");
	}
}
////////////////////////////////////////
function copy_reportid()
{

	var wartosc = $('[id="reportid"]').val();
	if (null == wartosc) {
		tekst = ' ';
	} else {
		if (document.getElementById('ifchosen').checked) {
			var tekst = "#REP_NR#" + wartosc + "#REP_NR_END##ONLY#";
		} else {
			var tekst = "#REP_NR#" + wartosc + "#REP_NR_END#";
		}
	}
	$('[id="7"]').val(tekst);
}

function htmlEncode(value) {
	if (value) {
		return jQuery('<div />').text(value).html();
	} else {
		return '';
	}
}
////////////////////////////////////////
window.onload = function () {
	var wartosc = $('input[name="moduleid"]').val();
	$.get("index.php?module=OSSPdf&action=OSSPdfAjax&mode=Popup&selected_module=" + wartosc, function (data) {
		var elements = $('[id="div_2"]');
		elements.each(function () {
			var ex = $(this).parent().html();
			ex = ex + data.result;
			$(this).parent().html(ex);
			//$('[name="content"]').val( $(this).val() );
		});

		$("#templates").change(function () {
			var obiekt = document.getElementsByName('moduleid');
			var nr = obiekt[0].selectedIndex;

			var txt = obiekt[0].options[ nr ].value;
			$.get("index.php?module=OSSPdf&action=OSSPdfAjax&mode=Popup&moduleid=" + txt + "&filename=" + $('#templates').val(), function (data) {
				$("#templates option:selected").each(function () {
					if ($(this).attr('name') != 'start') {
						CKEDITOR.instances.templatecontent.setData(data.result);
					}
				});
			});
		});

		if ($('[name="content"]').length)
		{
			//CKEDITOR.replace( 'content' );
		}
	});


	if ($('[name="header_content"]').length)
	{
		//CKEDITOR.replace( 'header_content' );
	}

	if ($('[name="footer_content"]').length)
	{
		//CKEDITOR.replace( 'footer_content' );
	}
	//Modification for constraints textarea
	url = "index.php?module=OSSPdf&action=OSSPdfAjax&mode=Popup_constraints&selected_module=" + wartosc;
	$.get(url, function (data) {
		var text = $('[name="constraints"]').val();
		$('[name="constraints"]').parent().html(data.result);
		$('[name="constraints"]').val(text);
	});
	//Modyfication for field dedicated to choose a module
	$.get("index.php?module=OSSPdf&action=OSSPdfAjax&file=ShowModuleIdField&selected_module=" + wartosc, function (data) {
		$('input[name="moduleid"]').parent().html(data.result);
	});
}


