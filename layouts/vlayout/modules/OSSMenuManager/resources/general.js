/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery( function() {
    reloadLibraries();
});

function saveFields() {

	var contents = jQuery('#my-tab-content').find('#sortable-box');
	var progressIndicatorElement = jQuery.progressIndicator({'position' : 'html','blockInfo' : {'enabled' : true}});
    
    // znajdź upuszczony element
    var dropElem = jQuery('div').find("[data-fields-id='" + window.droppedElemId + "']");
    var prevElemSeq = dropElem.prev().data('fields-seq');
    var nextElemSeq = dropElem.next().data('fields-seq');
    var presentElemSec = 1000;
	var differentBlock=true;
    var index = presentElemSec;
    dropElem.prevAll().each(function(e) {
        index = index - 1;
        jQuery(this).attr('data-fields-seq', index );
    });

    index = presentElemSec;
    dropElem.nextAll().each(function(e) {
        index = index + 1;
        jQuery(this).attr('data-fields-seq', index );
    });
    dropElem.attr('data-fields-seq', presentElemSec );
    var dropBlockId = dropElem.parents('.blockSortable').data('group-id'); // blok upuszczenia pola
	var newSequence = new Array()
	contents.find('.blockSortable').each(function(blockIndex,blockDomElement){////wykonujesz sie co blok
		var blockTable = jQuery(blockDomElement);
		var blockId = blockTable.attr('data-group-id');
        contents.find('.block_'+blockId).each(function(fildsIndex,fildsDomElement){ //przelatuje wszystkie pola w bloku
			var fildsTable = jQuery(fildsDomElement);

			var blockId2 = fildsTable.attr('data-group-id');
			var fieldId = fildsTable.attr('data-fields-id'); 
			var seqNum = fildsTable.attr('data-fields-seq'); 

            var expectedBlockSequence = '';
            if ( typeof dropBlockId == "undefined" ){
                expectedBlockSequence = (fildsIndex+1);
			//	differentBlock=false;
				}				
            else
                expectedBlockSequence = seqNum;
            if ( fieldId == dropElem.data('fields-id') && blockId == dropElem.data('group-id') && blockId2 == dropElem.data('group-id') ) {
                blockId2 = dropBlockId;
            }
            newSequence.push({'block':blockId2,'fieldId':fieldId,'sequence':expectedBlockSequence});
		});
	});

	var element = jQuery('<div></div>');
	var detailContainer = jQuery('div.contentsDiv');
   element.progressIndicator({
    'position':'html',
    'blockInfo' : {
     'enabled' : differentBlock,
     'elementToBlock' : detailContainer
    }
   });
   
	var params = {}
	params.data = {module: 'OSSMenuManager', action: 'SaveFields', 'group_id': dropBlockId, 'newSequence' : newSequence}
	params.async = false;
	params.dataType = 'json';
	AppConnector.request(params).then(
		function(data) {
			var params = {
				text: data.result,
				type: 'success'
			};
			Vtiger_Helper_Js.showPnotify(params);
		},
		function(data,err){
		element.progressIndicator({'mode': 'hide'});
		}
	);
	if(differentBlock==true){
		var params = {};
		params['module'] = 'OSSMenuManager';
		params['view'] = 'Configuration';
		params['parent'] = 'Settings';
		AppConnector.request(params).then(
			function(data) {
		jQuery('.contentsDiv').html(data);
		reloadLibraries();
			}
		);
	}

	progressIndicatorElement.progressIndicator({'mode': 'hide'});
}

function saveBlocks() {
	var contents = jQuery('#my-tab-content').find('#sortable-box');
	var progressIndicatorElement = jQuery.progressIndicator({'position' : 'html','blockInfo' : {'enabled' : true}});
	var newSequence = {};
	contents.find('.blockSortable').each(function(index,domElement){
		var blockTable = jQuery(domElement);
		var blockId = blockTable.attr('data-group-id');
		var expectedBlockSequence = (index+1);
		newSequence[blockId] = expectedBlockSequence;
	});
	var params = {};
	params.data = {
		'module' : "OSSMenuManager",
		'action' : "SaveBlocks",
		'newSequence' : newSequence
	};
	params.async = false;
	params.dataType = 'json';
	AppConnector.request(params).then(
		function(data) {
			var params = {
				text: data.result,
				type: 'success'
			};
			Vtiger_Helper_Js.showPnotify(params);
		},
		function(data,err){
		
		}
	);
	progressIndicatorElement.progressIndicator({'mode': 'hide'});
}

function openMenuCreation(blockId) {
	var data = { 'url': 'index.php?module=OSSMenuManager&view=MenuCreation&block='+blockId}
	app.showModalWindow(data);
}

function openMenuEdition( menuId ) {
	var data = { 'url': 'index.php?module=OSSMenuManager&view=MenuEdition&id='+menuId };
	app.showModalWindow(data);
}

function openBlockEdition( blockId ) {
	var data = { 'url': 'index.php?module=OSSMenuManager&view=BlockEdition&id='+blockId };
	app.showModalWindow(data);
}
function openLangEdition( blockId ) {
	var data = { 'url': 'index.php?module=OSSMenuManager&view=LangEdition&id='+blockId };
	app.showModalWindow(data);
}
function reloadLibraries() {
    var active = true;
    window.droppedElemId = false;
    window.droppedFromBlock = false;
	var contents = jQuery('#my-tab-content').find('#sortable-box');
	var table_block = contents.find('.blockSortable');
	contents.sortable({
		'containment' : contents,
		'items' : table_block,
		'revert' : true,
		'tolerance':'pointer',
		'cursor' : 'move',
		'update' : function(e, ui) {
			saveBlocks();
		}
	});
	var table_fields = contents.find('.editFieldsTable');
	table_fields.sortable({
		'containment' : contents,
		'revert' : true,
		'tolerance':'pointer',
		'cursor' : 'move',
		'placeholder': "state-highlight",
		'connectWith' : '.connectedSortable',
		'stop': function (event, ui ) {
			saveFields();
		},
        receive: function(e, ui) {
            droppedFromBlock = jQuery(ui.item).data('group-id');
            droppedElemId = jQuery(ui.item).data('fields-id');
            return true;
        }
	});
/*	
	var i=0;
	var licznik	= Array();
	 jQuery('.blockSortable').each(function(){
		 licznik[i] = jQuery(this).data('group-licznik');
		 
		 i++;
	//var licz = jQuery('div').find("[data-fields-licznik='" + licznik + "']");	 
	});
	console.log(licznik);
	
	
	//console.log(licznik);
	//var licznik = jQuery('.blockSortable').data('group-licznik');
		//licznik;.attr("[data-group-licznik='" + licznik[i] + "']")
		licznik[i] = jQuery(this).data('group-licznik');
	
	
    var height = '';
	table_block.each(function(Index,DomElement){
		var Table = jQuery(DomElement);
		console.log(Table);
		if(height < Table.height()){
			height = Table.height();
		}
		 i++;
		jQuery('.blockSortable').find("[data-group-licznik='" + licznik[i] + "']").height(height); 
	});
	//jQuery('.blockSortable').data('group-licznik').height(height);
	//jQuery('.blockSortable').height(height);
	*/
}

function reloadConfiguration() {
    var params = {};

    params['module'] = 'OSSMenuManager';
    params['view'] = 'Configuration';
    params['parent'] = 'Settings';    
    
    AppConnector.request(params).then(
        function(data) {
            jQuery('div.contentsDiv').html( data );
            reloadLibraries();
        }
    );
}
function setLabel(input, FieldName)
{
	var cursor_position = $(input).caret();
		
	var value = $(input).val().replace(/[^0-9a-zA-Z]/g, '_');
	
	$("input[name='"+FieldName+"']").val(value);
	
	$(input).caret(cursor_position);
}
///////////////////////////////////
jQuery( function() {
    jQuery('#config_button').click( function(e) {
        var ret = CheckShortcutsForm();
        
        if ( ret == false )
            e.preventDefault();
    });
    
    // modal is greyed out if z-index is low
    $("#myModal").css("z-index", "9999999");
    
    // Hide modal if "Okay" is pressed
    $('#myModal .okay-button').click(function() {
        var disabled = $('#confirm').attr('disabled');
        if(typeof disabled == 'undefined') {
            $('#myModal').modal('hide');
            $('#delete #EditView').submit();
        }
    });
    
    // enable/disable confirm button
    $('#status').change(function() {
        $('#confirm').attr('disabled', !this.checked);
    });
});