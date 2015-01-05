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
<h3 id="myModalLabel">Edycja {vtranslate($MENU_RECORD.label, 'OSSMenuManager')}</h3>
</div>
<div class="modal-body" style="width: auto;">
	<div class="modal-Fields" style="padding-right:30px;">
		<div class="row-fluid">
			<div class="span5 marginLeftZero">{vtranslate('LBL_MENUTYPE', 'OSSMenuManager')}:</div>
			<div class="span7">{vtranslate($MENU_RECORD.type, 'OSSMenuManager')}</div>
		</div>
        <input type="hidden" name="menu_id" value="{$MENU_RECORD.id}" />
        {if $MENU_RECORD.type eq 'LBL_module'}
            <div id="module_div" class="row-fluid">
                <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_MODULE', 'OSSMenuManager')}:</label></div>
                <div class="span7"><select name="module_modules" class="chzn-select span3">
                    {foreach from=$MODULES item=item}
                        <option data-modulename="{$item['name']}" value="{$item['tabid']}" 
                            {if $MENU_RECORD.label eq $item['name']}
                                selected="selected" 
                            {/if}
                        >{vtranslate($item['name'], $item['name'])}</option>
                    {/foreach}</select></div>
            </div>
        {elseif $MENU_RECORD.type eq 'LBL_shortcut'}
            <div id="shortcut_div" class="row-fluid">
                <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_NAME', 'OSSMenuManager')}:</label></div>
                <div class="span7"><input name="shortcut_label" class="span3" type="text" value="{$MENU_RECORD.label}" /></div>		
                <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_URL', 'OSSMenuManager')}:</label></div>
                <div class="span7"><input name="shortcut_url" class="span3" type="text" value="{$MENU_RECORD.url}" /></div>	
            </div>
        {elseif $MENU_RECORD.type eq 'LBL_label'}
            <div id="label_div" class="row-fluid">
                <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_NAME', 'OSSMenuManager')}:</label></div>
                <div class="span7"><input name="label_label" class="span3" type="text" value="{$MENU_RECORD.label}" /></div>	
            </div>
        {elseif $MENU_RECORD.type eq 'LBL_script'}
            <div id="script_div" class="row-fluid">
                <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_NAME', 'OSSMenuManager')}:</label></div>
                <div class="span7"><input name="script_label" class="span3" type="text" value="{$MENU_RECORD.label}" /></div>	
                <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_script', 'OSSMenuManager')}:</label></div>
                <div class="span7"><textarea name="script_url">{$MENU_RECORD.url}</textarea></div>	
            </div>
        {/if}
        <div id="rest_div" class="row-fluid">
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
        <div id="rest2_div" class="row-fluid">  
		{if $MENU_RECORD.type ne 'LBL_separator'}
            <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_WINDOWNEW', 'OSSMenuManager')}:</label></div>
            <div class="span7"><input name="new_window" class="span3" type="checkbox" value="1" {if $MENU_RECORD.new_window eq 1} checked="checked" {/if}/></div><br /><br />
		{/if}
            <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_WIDOCZNE', 'OSSMenuManager')}:</label></div>
            <div class="span7"><input name="visible" class="span3" type="checkbox" value="1" {if $MENU_RECORD.visible eq 1} checked="checked" {/if}/></div><br /><br />
        </div>
		{if $MENU_RECORD.type ne 'LBL_separator'}
		<div id="color_div" class="row-fluid">
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_MENUCOLOR', 'OSSMenuManager')}:</label></div>
			<div class="span7">
				<p class="colorPickerDiv"></p>
				<input name="color" class="span3 colorPicker" type="text" value="{$MENU_RECORD.color}">
			</div>
		</div>
		<div id="location_div" class="row-fluid">	
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_LOCATIONICON', 'OSSMenuManager')}:</label></div>
			<div class="span7"><input name="location_icon" class="span3" type="text" value="{$MENU_RECORD.locationicon}"></div>
		</div>
		<div id="select_div" class="row-fluid">
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_SIZEICON', 'OSSMenuManager')}:</label></div>
			<div class="span7"><select name="select_icon" class="chzn-select span3">
			<option 
				{if $MENU_RECORD.sizeicon eq '16x16'}
                    selected="selected" 
                {/if}> 16x16</option>
			<option{if $MENU_RECORD.sizeicon eq '24x24'}
                    selected="selected" 
                {/if}>24x24</option>
			<option{if $MENU_RECORD.sizeicon eq '32x32'}
                    selected="selected" 
                {/if}>32x32</option></select></div>
		</div>
		{/if}
	</div>
</div>
<div class="modal-footer">
<button class="btn" id="closeModal" data-dismiss="modal" aria-hidden="true">{vtranslate('LBL_CLOSE', 'OSSMenuManager')}</button>
<button class="btn addButton" onclick="editMenu();" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SAVE', 'OSSMenuManager')}</button>
<button class="btn btn-danger" onclick="deleteMenu();" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_DELETE', 'OSSMenuManager')}</button>
</div>
{literal}
<script>
function deleteMenu() {
    var selectVal = jQuery('[name="types_menu"] :selected').val();
    var menuId = '{/literal}{$MENU_RECORD.id}{literal}';
    
    var params = {};
    params.async = false;
    params.dataType = 'json';
    params.data = { 
        'module' : "OSSMenuManager",
        'action' : "DeleteMenu",
        'id' : menuId,
    };
    
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
    
    return true;
}

function editMenu() {
    var selectVal = jQuery('[name="types_menu"] :selected').val();
    var menuType = '{/literal}{$MENU_RECORD.type}{literal}';
    var menuId = '{/literal}{$MENU_RECORD.id}{literal}';
    var visible = 0;
    var newWindow = 0;
	var parentId = '{/literal}{$MENU_RECORD.parent_id}{literal}';
    var roles = new Array();

    
    jQuery('[name="priviledge_list"] :selected').each( function() {
        roles.push( jQuery(this).val() );
    });
    
    roles = ' ' + roles.join(' |##| ') + ' ';
   // console.log(roles);
    if ( jQuery('[name="visible"]').is(':checked') == true )
        visible = 1;
    if ( jQuery('[name="new_window"]').is(':checked') == true )
        newWindow = 1;
   
    switch( menuType ) {
        case 'LBL_module': // Module
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
                'action' : "EditMenu",
                'type' : 'module',
                'id' : menuId,
                'tabId' : moduleId,
                'label' : moduleName,
                'visible' : visible,
                'url' : 'index.php?module=' + moduleName + '&view=List',
                'newWindow' : newWindow,
                'permission' : roles,
				'locationicon' : locationIcon,
				'color' : color,
				'sizeicon' : sizeIcon,
				'parent_id': parentId
            }
        break;
        
        case 'LBL_shortcut': // shortcut_div
            var shortcut_label = jQuery('[name="shortcut_label"]').val();
            var shortcut_url = jQuery('[name="shortcut_url"]').val(); 
			var sizeIcon = jQuery('[name="select_icon"]').val();
			var locationIcon = jQuery('[name="location_icon"]').val();
			var color = jQuery('[name="color"]').val();
      //  console.log(shortcut_label);    
            var params = {};
            params.async = false;
            params.dataType = 'json';
            params.data = { 
                'module' : "OSSMenuManager",
                'action' : "EditMenu",
                'type' : 'shortcut',
                'id' : menuId,
                'tabId' : 0,
                'label' : shortcut_label,
                'visible' : visible,
                'url' : shortcut_url,
                'newWindow' : newWindow,
                'permission' : roles,
				'locationicon' : locationIcon,
				'color' : color,
				'sizeicon' : sizeIcon,
				'parent_id': parentId
            }
        break;
        
        case 'LBL_label': // label
            var label = jQuery('[name="label_label"]').val();
			var sizeIcon = jQuery('[name="select_icon"]').val();
			var locationIcon = jQuery('[name="location_icon"]').val();
            var color = jQuery('[name="color"]').val();
			
            var params = {};
            params.async = false;
            params.dataType = 'json';
            params.data = { 
                'module' : "OSSMenuManager",
                'action' : "EditMenu",
                'type' : 'label',
                'id' : menuId,
                'tabId' : 0,
                'label' : label,
                'visible' : visible,
                'url' : '*etykieta*',
                'newWindow' : newWindow,
                'permission' : roles,
				'locationicon' : locationIcon,
				'color' : color,
				'sizeicon' : sizeIcon,
				'parent_id': parentId
            }
        break;
        
        case 'LBL_separator': // separator
	//		var sizeIcon = jQuery('[name="select_icon"]').val();
	//		var locationIcon = jQuery('[name="location_icon"]').val();
            var params = {};
            params.async = false;
            params.dataType = 'json';
            params.data = { 
                'module' : "OSSMenuManager",
                'action' : "EditMenu",
                'type' : 'separator',
                'id' : menuId,
                'tabId' : 0,
                'label' : '*separator*',
                'visible' : visible,
                'url' : '',
                'newWindow' : newWindow,
                'permission' : roles
	//			'locationicon' : locationIcon,
	//			'sizeicon' : sizeIcon
            }
        break;
        
        case 'LBL_script': // script
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
                'action' : "EditMenu",
                'type' : 'script',
                'id' : menuId,
                'tabId' : 0,
                'label' : script_label,
                'visible' : visible,
                'url' : script_url,
                'newWindow' : newWindow,
                'permission' : roles,
				'locationicon' : locationIcon,
				'color' : color,
				'sizeicon' : sizeIcon,
				'parent_id': parentId
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
    
    return true;
}
</script>
{/literal}			