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
<style>
ol{
  list-style-type:none;
  text-align:left;
  }
ol>li{
float:left;
}

.error {
text-align:right;
}
</style>
<script type="text/javascript" src="layouts/vlayout/modules/OSSMenuManager/resources/jqueryCaret.js"></script>
<script type="text/javascript" src="layouts/vlayout/modules/OSSMenuManager/resources/PopupUtils.js"></script>


<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

{if $BLOCKID eq 0}
    <h3 id="myModalLabel">{vtranslate('Tworzenie nowego bloku', 'OSSMenuManager')}</h3>
{else}
    <h3 id="myModalLabel">{vtranslate('Edycja bloku', 'OSSMenuManager')}</h3>
{/if}

</div>

<div class="modal-body" style="width: auto;">
<table>

<tr valign="top">
	<td style="margin-top:20px; ">
	<div class="modal-Fields" >
		<div class="row-fluid">
			<div class="span5 marginLeftZero">{vtranslate('LBL_BLOCKNAME', 'OSSMenuManager')}:</div>
			{if $BLOCKID eq 0}
			<div class="span7"><input type="text" name="block_title" onkeyup="setLabel(this, '{$LANGP}')"/>
			{else}
			<div class="span7"><input type="text" name="block_title" value="{$BLOCK_RECORD.label}"/>
			{/if}
			</div>
        <div id="permissions" class="row-fluid">
            <div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_PRIVILEDGES', 'OSSMenuManager')}:</label></div>
            <div class="span7">
                <select name="priviledge_list" class="chzn-select span3" multiple="multiple">
					{foreach from=$PROFILES item=item key=key}
						<option value="{$key}" 
						{foreach from=$BLOCK_RECORD.acces item=item2}
						{if $item2 eq $key} selected="selected" {/if}
						{/foreach}>{vtranslate($item, 'OSSMenuManager')}</option>
					{/foreach}
				</select>
            </div>
        </div>
        <div class="row-fluid">
			<div class="span5 marginLeftZero">{vtranslate('LBL_WIDOCZNE', 'OSSMenuManager')}:</div>
			<div class="span7"><input name="visible" class="span3" type="checkbox" value="1" {if $BLOCK_RECORD.visible eq 1} checked="checked" {/if}/></div><br /><br />
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_LOCATIONICON', 'OSSMenuManager')}:</label></div>
			<div class="span7"><input name="location_icon" class="span3" type="text" value="{$BLOCK_RECORD.locationicon}" ></div>
        </div>
		
		<div id="select_div" class="row-fluid">
			<div class="span5 marginLeftZero"><label class="">{vtranslate('LBL_SIZEICON', 'OSSMenuManager')}:</label></div>
			<div class="span7"><select name="select_icon" class="chzn-select span3">
			<option 
				{if $BLOCK_RECORD.sizeicon eq '16x16'}
                    selected="selected" 
                {/if}> 16x16</option>
			<option {if $BLOCK_RECORD.sizeicon eq '24x24'}
                    selected="selected" 
                {/if}>24x24</option>
			<option {if $BLOCK_RECORD.sizeicon eq '32x32'}
                    selected="selected" 
                {/if}>32x32</option></select></div>
		</div>

		
		</div>
	</div>
	</div>
	
		
		
	</td>
	{$i=1}
	
	
		
		<!--<div class="row-fluid" style="margin-right:0px;  padding-right:0px; background-color:#EEEEEE">
		<div class="row-fluid">-->
		
			
		<td style="padding-left: 60px; width:auto;">
	<!--	<label>{vtranslate('LBL_BLOCKNAME', 'OSSMenuManager')}</label>-->
		<div style="padding-right:0px"> 
	{foreach from=$LANG item=item key=key}
	
	{if $i eq '6'}
	</td>
	<td style="padding-left: 2px; width:auto;">
	{$i=1}
	{/if}
			
			{$q=0}
			{foreach from=$LANGV item=item2 key=key2}
			
			{if $key eq $key2}
				<div class="span5" style="width: 150px; padding:0px;"><div style="display:none">{$i++}. </div>{$item.label}:</div>	
				<input class="translang" style="max-height: 20px; max-width: 140px; padding: 0px; font-size: 90%;" type="text" name="{$key}" placeholder="{$item.label}" value="{$item2}" /><br />
				{$q=1}
			{/if}
			{/foreach}
			{if $q!=1}	
				<div class="span5" style="width: 150px; padding:0px;"><div style="display:none">{$i++}. </div>{$item.label}:</div>	
				<input class="translang" style="max-height: 20px; max-width: 140px; padding:0px; font-size: 90%;" type="text" name="{$key}" placeholder="{$item.label}"  /><br />
			{/if}	
				
			
			
		</div>
		
		</div>
	{/foreach}
	</td>
</tr>

</table>
</div>

<div style="margin-left:20px; margin-bottom:20px;  bottom: 20px;">
		<button class="btn" id="closeModal" data-dismiss="modal" aria-hidden="true">{vtranslate('LBL_CLOSE', 'OSSMenuManager')}</button>
			{if $BLOCKID eq 0}
		<button class="btn addButton" onclick="addBlock();" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_ADDBLOCK', 'OSSMenuManager')}</button>
			{else}
		<button class="btn addButton" onclick="editBlock();" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SAVE', 'OSSMenuManager')}</button>
		<button class="btn btn-danger" onclick="deleteBlock();" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_DELETE', 'OSSMenuManager')}</button>
			{/if}
		</div>
{literal}
<script>
/*
jQuery.ready(function(){
$(this).find('input.translang').each(function(){
    var filter = /[*]/;
    if (!filter.test($(this).val())){
        alert('ok');
    } else {
        alert('błąd');
    }
});
});


 var reg =/[*]/;  
  var wynik = '';
  jQuery.ready(function(){
  jQuery('.translang').each(function(){
  var tr = jQuery(this).val();
  wynik = tr.test(reg);
  if(wynik!=null){alert('zawiera błedy');};
  });
  )};

jQuery.ready(function(){
jQuery('.translang:contains("rap")').addClass('error');
});*/
function addBlock() {
	
    var blockName = jQuery('[name="block_title"]').val();
    var visible = 0;
    var roles = new Array();
	var sizeIcon = jQuery('[name="select_icon"]').val();
	var locationIcon = jQuery('[name="location_icon"]').val();


	//var langField = jQuery('[name="langfield"]').val();
	var translang= Array();
	var i=0;
	
	
	jQuery('.translang').each(function(){
  //  console.log(jQuery(this).val());
	if(jQuery(this).val() != '')
	{
	translang[i]=jQuery(this).attr('name')+'*'+jQuery(this).val();
	i++;
	}
	}); 
	//console.log(translang);
	translang=translang.join('#');

   
    jQuery('[name="priviledge_list"] :selected').each( function() {
        roles.push( jQuery(this).val() );
    });
    
    roles = ' ' + roles.join(' |##| ') + ' ';
    console.log(roles);
    if ( jQuery('[name="visible"]').is(':checked') == true )
        visible = 1;
    
    if ( blockName.length === 0 ) {
        var parametry = {
            text: '{/literal}{vtranslate("MSG_BLOCKNAME_ERROR", 'OSSMenuManager')}{literal}',
            type: 'error'
        };
        Vtiger_Helper_Js.showPnotify(parametry);
        return false;
    }
    
	var status=true;
	var filter = /[*#]/;
	jQuery('.translang').each(function(){
		if (filter.test($(this).val())){
			var par = {
				text: app.vtranslate('{/literal}{vtranslate("MSG_Translations_ERROR", 'OSSMenuManager')}{literal}'),
				type: 'error'
			};
			Vtiger_Helper_Js.showPnotify(par);
			status=false;
		}
	});
	if(status==false)
	{return false;}

	
    var params = {};
    params.async = false;
    params.dataType = 'json';
    params.data = { 
        'module' : "OSSMenuManager",
        'action' : "AddBlock",
        'name' : blockName,
        'visible' : visible,
        'permission' : roles,
		'locationicon' : locationIcon,
		'sizeicon' : sizeIcon,
		'langfield' : translang
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

function editBlock() {
    var blockName = jQuery('[name="block_title"]').val();
    var blockId = '{/literal}{$BLOCKID}{literal}';
    var visible = 0;
    var roles = new Array();
	var sizeIcon = jQuery('[name="select_icon"]').val();
	var locationIcon = jQuery('[name="location_icon"]').val();
    
    jQuery('[name="priviledge_list"] :selected').each( function() {
        roles.push( jQuery(this).val() );
    });
    
    roles = ' ' + roles.join(' |##| ') + ' ';
    
    if ( jQuery('[name="visible"]').is(':checked') == true )
        visible = 1;
    
    if ( blockName.length === 0 ) {
        var parametry = {
            text: app.vtranslate('{/literal}{vtranslate("MSG_BLOCKNAME_ERROR", 'OSSMenuManager')}{literal}'),
            type: 'error'
        };
        Vtiger_Helper_Js.showPnotify(parametry);
        return false;
    }
    
	var translang= Array();
	var i=0;
	jQuery('.translang').each(function(){
//	jQuery('(this):contains(rap)').css('border','1px solid red');
	if(jQuery(this).val() != '')
	{
		
		
		translang[i]=jQuery(this).attr('name')+'*'+jQuery(this).val();
		i++;
	}
	});  
	translang=translang.join('#');
///////////// 

var status=true;
var filter = /[*#]/;
jQuery('.translang').each(function(){
    if (filter.test($(this).val())){
		var par = {
            text: app.vtranslate('{/literal}{vtranslate("MSG_Translations_ERROR", 'OSSMenuManager')}{literal}'),
            type: 'error'
        };
        Vtiger_Helper_Js.showPnotify(par);
		status=false;
    }
});
 if(status==false)
 {return false;}

/////////////		
    var params = {};
    params.async = false;
    params.dataType = 'json';
    params.data = { 
        'module' : "OSSMenuManager",
        'action' : "EditBlock",
        'id' : blockId,
        'name' : blockName,
        'visible' : visible,
        'permission' : roles,
		'locationicon' : locationIcon,
		'sizeicon' : sizeIcon,
		'langfield' : translang
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

function deleteBlock() {
    var selectVal = jQuery('[name="types_menu"] :selected').val();
    var blockId = '{/literal}{$BLOCKID}{literal}';
    var blockItemNum = '{/literal}{$BLOCKITEMNUM}{literal}';
    
    if ( blockItemNum > 0 ) {
        var parametry = {
            text: '{/literal}{vtranslate("MSG_BLOCKNOTEMPTY_ERROR", 'OSSMenuManager')}{literal}',
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
        'action' : "DeleteBlock",
        'id' : blockId,
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
</script>
{/literal}