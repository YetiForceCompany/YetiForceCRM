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
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3 id="myModalLabel">{vtranslate('LBL_CREATINGMENU', 'OSSMenuManager')}</h3>
</div>
<div class="modal-body" style="width: 400px;">
	<div class="modal-Fields">
		<div class="row-fluid">
			<div class="span5 marginLeftZero">{vtranslate('Blok menu', 'OSSMenuManager')}:</div>
			<div class="span7"><select name="block" class="chzn-select span3">
				{foreach from=$BLOCK item=item}
					<option value="{$item['id']}"
						{if $MENU_RECORD.id eq $item['id']}
                                selected="selected" 
                        {/if}					
					>{vtranslate($item['label'], 'OSSMenuManager')}</option>
				{/foreach}</select></div>
			<div class="span5 marginLeftZero">{vtranslate('Typ menu', 'OSSMenuManager')}:</div>
			<div class="span7 marginTop">
				<select name="types_menu" class="chzn-select span3" onchange='changeFields();'>
					{foreach from=$TYPES item=item key=key}
						<option value="{$key}">{vtranslate($item, 'OSSMenuManager')}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div id="module_div" class="row-fluid">
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_MODULE', 'OSSMenuManager')}:</label></div>
			<div class="span7"><select name="module_modules" class="chzn-select span3">{foreach from=$MODULES item=item}<option data-modulename="{$item['name']}" value="{$item['tabid']}">{vtranslate($item['name'], $item['name'])}</option>{/foreach}</select></div>
		</div>
		<div id="shortcut_div" class="row-fluid hide">
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_NAME', 'OSSMenuManager')}:</label></div>
			<div class="span7"><input name="shortcut_label" class="span3" type="text" disabled="disabled" /></div>		
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_URL', 'OSSMenuManager')}:</label></div>
			<div class="span7"><input name="shortcut_url" class="span3" type="text" disabled="disabled" /></div>	
		</div>
		<div id="label_div" class="row-fluid hide">
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_NAME', 'OSSMenuManager')}:</label></div>
			<div class="span7"><input name="label_label" class="span3" type="text" disabled="disabled" /></div>
            
		</div>
		<div id="script_div" class="row-fluid hide">
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_NAME', 'OSSMenuManager')}:</label></div>
			<div class="span7"><input name="script_label" class="span3" type="text" disabled="disabled" /></div>	
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_script', 'OSSMenuManager')}:</label></div>
			<div class="span7"><textarea name="script_url" disabled="disabled">javascript:</textarea></div>	
		</div>
        <div id="priviledge_div" class="row-fluid">
            <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_PRIVILEDGES', 'OSSMenuManager')}:</label></div>
            <div class="span7">
                <select name="priviledge_list" class="chzn-select span3" multiple="multiple">
					{foreach from=$PROFILES item=item key=key}
						<option value="{$key}"
						{foreach from=$MENU_RECORD.acces item=item2}
						{if $item2 eq $key} selected="selected" {/if}
						{/foreach}>{vtranslate($item, 'OSSMenuManager')}</option>
					{/foreach}
				</select>
            </div>
        </div>
		<div id="color_div" class="row-fluid">
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_MENUCOLOR', 'OSSMenuManager')}:</label></div>
			<div class="span7"><input name="color" class="span3" type="text"></div>
		</div>
        <div id="newwinwod_div" class="row-fluid">        
            <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_WINDOWNEW', 'OSSMenuManager')}:</label></div>
            <div class="span7"><input name="new_window" class="span3" type="checkbox" value="1" {if $MENU_RECORD.new_window eq 1} checked="checked" {/if}/></div><br /><br />
        </div>
        <div id="visible_div" class="row-fluid">  
            <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_WIDOCZNE', 'OSSMenuManager')}:</label></div>
            <div class="span7"><input name="visible" class="span3" type="checkbox" value="1" {if $MENU_RECORD.visible eq 1} checked="checked" {/if}/></div><br /><br />
		<div id="location_div" class="row-fluid">	
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_LOCATIONICON', 'OSSMenuManager')}:</label></div>
			<div class="span7"><input name="location_icon" class="span3" type="text"></div>
		</div>
	   </div>
		<div id="select_div" class="row-fluid">
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_SIZEICON', 'OSSMenuManager')}:</label></div>
			<div class="span7"><select name="select_icon" class="chzn-select span3">
			<option>16x16</option><option>24x24</option><option>32x32</option></select></div>
		</div>
	</div>
</div>
<div class="modal-footer">
<button class="btn" id="closeModal" data-dismiss="modal" aria-hidden="true">{vtranslate('LBL_CLOSE', 'OSSMenuManager')}</button>
<button class="btn addButton" onclick="addMenu();" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SAVE', 'OSSMenuManager')}</button>
</div>
{literal}
<script>
function addMenu() {
    var selectVal = jQuery('[name="types_menu"] :selected').val();
    var blockId = '{/literal}{$smarty.get.block}{literal}';
    var visible = 0;
    var newWindow = 0;
    var roles = new Array();
    
    jQuery('[name="priviledge_list"] :selected').each( function() {
        roles.push( jQuery(this).val() );
    });
    
    roles = ' ' + roles.join(' |##| ') + ' ';
    
    if ( jQuery('[name="visible"]').is(':checked') == true )
        visible = 1;
    if ( jQuery('[name="new_window"]').is(':checked') == true )
        newWindow = 1;    
    
    switch( parseInt(selectVal) ) {
        case 0: // Module
            var moduleId = jQuery('[name="module_modules"]').val();            
            var moduleName = jQuery('[name="module_modules"] option:selected').data('modulename');
            var sizeIcon = jQuery('[name="select_icon"]').val();
			var locationIcon = jQuery('[name="location_icon"]').val();
			var color = jQuery('[name="color"]').val();
			
            var params = {};
            params.async = false;
            params.dataType = 'json';
            params.data = { 
                'module' : "OSSMenuManager",
                'action' : "AddMenu",
                'blockId' : blockId,
                'type' : 'module',
                'tabId' : moduleId,
                'label' : moduleName,
                'sequence' : -1,
                'visible' : visible,
                'url' : 'index.php?module=' + moduleName + '&view=List',
                'newWindow' : newWindow,
                'permissions' : roles,
				'locationicon' : locationIcon,
				'color' : color,
				'sizeicon' : sizeIcon
            }
        break;
        
        case 1: // shortcut_div
            var shortcut_label = jQuery('[name="shortcut_label"]').val();
            var shortcut_url = jQuery('[name="shortcut_url"]').val(); 
            var sizeIcon = jQuery('[name="select_icon"]').val();
			var locationIcon = jQuery('[name="location_icon"]').val();			
            var color = jQuery('[name="color"]').val();
			
			var tab = Array('index','http://', 'https://', 'www');
			var statusOk = 0;
			for (var i=0;i<4;i++){
			//console.log(tab[i]);
				if(shortcut_url.lastIndexOf(tab[i]) !=-1)
				 statusOk =1;
			}

			if ( statusOk == 0 || shortcut_label.length === 0 ) {
				var parametry = {
				text: '{/literal}{vtranslate("MSG_URL_EMPTY", 'OSSMenuManager')}{literal}',
				type: 'error'
				};
				Vtiger_Helper_Js.showPnotify(parametry);
				return false;
			}
			
            var params = {};
            params.async = false;
            params.dataType = 'json';
            params.data = { 
                'module' : "OSSMenuManager",
                'action' : "AddMenu",
                'blockId' : blockId,
                'type' : 'shortcut',
                'tabId' : 0,
                'label' : shortcut_label,
                'sequence' : -1,
                'visible' : visible,
                'url' : shortcut_url,
                'newWindow' : newWindow,
                'permissions' : roles,
				'locationicon' : locationIcon,
				'color' : color,
				'sizeicon' : sizeIcon
            }
        break;
        
        case 2: // label
            var label = jQuery('[name="label_label"]').val();
			var sizeIcon = jQuery('[name="select_icon"]').val();
			var locationIcon = jQuery('[name="location_icon"]').val();	
			var color = jQuery('[name="color"]').val();
          //  var label_url = jQuery('[name="label_url"]').val();
            
            var params = {};
            params.async = false;
            params.dataType = 'json';
            params.data = { 
                'module' : "OSSMenuManager",
                'action' : "AddMenu",
                'blockId' : blockId,
                'type' : 'label',
                'tabId' : 0,
                'label' : label,
                'sequence' : -1,
                'visible' : visible,
                'url' : '*etykieta*',//+label_url,
                'newWindow' : newWindow,
                'permissions' : roles,
				'locationicon' : locationIcon,
				'color' : color,
				'sizeicon' : sizeIcon
            }
        break;
        
        case 3: // separator
		//	var sizeIcon = jQuery('[name="select_icon"]').val();
		//	var locationIcon = jQuery('[name="location_icon"]').val();
            var params = {};
            params.async = false;
            params.dataType = 'json';
            params.data = { 
                'module' : "OSSMenuManager",
                'action' : "AddMenu",
                'blockId' : blockId,
                'type' : 'separator',
                'tabId' : 0,
                'label' : '*separator*',
                'sequence' : -1,
                'visible' : visible,
                'url' : '*separator*',
                'newWindow' : 0,
                'permissions' : roles,
		//		'locationicon' : locationIcon,
		//		'sizeicon' : sizeIcon
            }
        break;
        
        case 4: // script
            var script_label = jQuery('[name="script_label"]').val();
            var script_url = jQuery('[name="script_url"]').val();
			var sizeIcon = jQuery('[name="select_icon"]').val();
			var locationIcon = jQuery('[name="location_icon"]').val();
            var color = jQuery('[name="color"]').val();
			
            var params = {};
            params.async = false;
            params.dataType = 'json';
            params.data = { 
                'module' : "OSSMenuManager",
                'action' : "AddMenu",
                'blockId' : blockId,
                'type' : 'script',
                'tabId' : 0,
                'label' : script_label,
                'sequence' : -1,
                'visible' : visible,
                'url' : script_url,
                'newWindow' : 0,
                'permissions' : roles,
				'locationicon' : locationIcon,
				'color' : color,
				'sizeicon' : sizeIcon
            }
        break;
    }
            
    AppConnector.request(params).then(
        function(data) {
            var result = data.result;
            
            if ( result.success === true ) {
                var parametry = {
                    text: result.return,
                    type: 'success'
                };
                Vtiger_Helper_Js.showPnotify(parametry);
            }
            else {
                var parametry = {
                    text: result.return,
                    type: 'error'
                };
                Vtiger_Helper_Js.showPnotify(parametry);
            }
        },
        function(data,err){
            var parametry = {
                text: app.vtranslate('JS_ERROR_CONNECTING'),
                type: 'error'
            };
            Vtiger_Helper_Js.showPnotify(parametry);
        }
    );
    
    reloadConfiguration();
    
    return false;
}

function changeFields() {
    var selectVal = jQuery('[name="types_menu"] :selected').val();
    
    switch( parseInt(selectVal) ) {
            case 0: // Module
				jQuery( "#module_div" ).show();                
				jQuery( "#shortcut_div" ).hide();
				jQuery( "#label_div" ).hide();
				jQuery( "#script_div" ).hide();
                jQuery( '#visible_div' ).show();
                jQuery( '#newwindow_div' ).show();
                jQuery( '#priviledge_div' ).show();
                
                jQuery('[name="module_modules"]').prop('disabled', false).trigger('liszt:updated');
                jQuery('[name="shortcut_label"]').prop('disabled', true);
                jQuery('[name="shortcut_url"]').prop('disabled', true);
                jQuery('[name="label_label"]').prop('disabled', true);
                jQuery('[name="label_url"]').prop('disabled', true);
                jQuery('[name="script_label"]').prop('disabled', true);
                jQuery('[name="script_url"]').prop('disabled', true);
            break;
            case 1: // shortcut_div
				jQuery( "#module_div" ).hide();
				jQuery( "#shortcut_div" ).show();
				jQuery( "#label_div" ).hide();
				jQuery( "#script_div" ).hide();
                jQuery( '#visible_div' ).show();
                jQuery( '#newwindow_div' ).show();
                jQuery( '#priviledge_div' ).show();
                
                jQuery('[name="module_modules"]').prop('disabled', true).trigger('liszt:updated');
                jQuery('[name="shortcut_label"]').prop('disabled', false);
                jQuery('[name="shortcut_url"]').prop('disabled', false);
                jQuery('[name="label_label"]').prop('disabled', true);
                jQuery('[name="label_url"]').prop('disabled', true);
                jQuery('[name="script_label"]').prop('disabled', true);
                jQuery('[name="script_url"]').prop('disabled', true);
            break;
            case 2: // label
				jQuery( "#module_div" ).hide();
				jQuery( "#shortcut_div" ).hide();
				jQuery( "#label_div" ).show();
				jQuery( "#script_div" ).hide();
                jQuery( '#visible_div' ).show();
                jQuery( '#newwindow_div' ).show();
                jQuery( '#priviledge_div' ).show();
                
                jQuery('[name="module_modules"]').prop('disabled', true).trigger('liszt:updated');
                jQuery('[name="shortcut_label"]').prop('disabled', true);
                jQuery('[name="shortcut_url"]').prop('disabled', true);
                jQuery('[name="label_label"]').prop('disabled', false);
                jQuery('[name="label_url"]').prop('disabled', false);
                jQuery('[name="script_label"]').prop('disabled', true);
                jQuery('[name="script_url"]').prop('disabled', true);
            break;
            case 3: // separator
				jQuery( "#module_div" ).hide();
				jQuery( "#shortcut_div" ).hide();
				jQuery( "#label_div" ).hide();
				jQuery( "#script_div" ).hide();
                jQuery( '#visible_div' ).show();
                jQuery( '#newwindow_div' ).hide();
                jQuery( '#priviledge_div' ).show();
				jQuery( "#select_div" ).hide();
				jQuery( "#location_div" ).hide();
                
                jQuery('[name="module_modules"]').prop('disabled', true).trigger('liszt:updated');
                jQuery('[name="shortcut_label"]').prop('disabled', true);
                jQuery('[name="shortcut_url"]').prop('disabled', true);
                jQuery('[name="label_label"]').prop('disabled', true);
                jQuery('[name="label_url"]').prop('disabled', true);
                jQuery('[name="script_label"]').prop('disabled', true);
                jQuery('[name="script_url"]').prop('disabled', true);
				jQuery('[name="select_icon"]').prop('disabled', true);
				jQuery('[name="location_icon"]').prop('disabled', true);
				jQuery('[name="new_window"]').prop('disabled', true);
				
            break;
            case 4: // script
				jQuery( "#module_div" ).hide();
				jQuery( "#shortcut_div" ).hide();
				jQuery( "#label_div" ).hide();
				jQuery( "#script_div" ).show();
                jQuery( '#visible_div' ).show();
                jQuery( '#newwindow_div' ).hide();
                jQuery( '#priviledge_div' ).show();
                
                jQuery('[name="module_modules"]').prop('disabled', true).trigger('liszt:updated');
                jQuery('[name="shortcut_label"]').prop('disabled', true);
                jQuery('[name="shortcut_url"]').prop('disabled', true);
                jQuery('[name="label_label"]').prop('disabled', true);
                jQuery('[name="label_url"]').prop('disabled', true);
                jQuery('[name="script_label"]').prop('disabled', false);
                jQuery('[name="script_url"]').prop('disabled', false);
            break;
        }
}
</script>
{/literal}