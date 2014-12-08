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
.drag-icon{
padding-top: 2%;
}
#pre-sortable-box{
position: absolute;
top: 50%;
left: 50%;
}
.row-fluid > [class*="span"]{
margin-left: 1%;
}
.row-fluid > [class*="span"].blockSortable:first-child {
margin-left: 1%;
}
.state-highlight{ height: 2em; line-height: 2em; border: 1px solid #FFD600;background-color:#F9FFB3; }
.not_visible { color:grey; text-decoration:line-through; }
.no_permission { color:red !important; }
.paddingTop3 { padding-top:3px; }

hr.style-one {

    border: 0;
    height: 1px;
    background: #333;
    background-image: -webkit-linear-gradient(left, #ccc, #333, #ccc); 
    background-image:    -moz-linear-gradient(left, #ccc, #333, #ccc); 
    background-image:     -ms-linear-gradient(left, #ccc, #333, #ccc); 
    background-image:      -o-linear-gradient(left, #ccc, #333, #ccc); 
	
}
</style>
<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-tab.js"></script>
<div class="container-fluid" id="layoutEditorContainer">
{if $ERROR neq ''}
    <div class="alert alert-error">
        <strong>{vtranslate('Error', $MODULENAME)}</strong> {vtranslate($ERROR, $MODULENAME)}
    </div>
{/if}
<div id="my-tab-content" class="tab-content" class="row-fluid">
    {* menu settings *}
    <div class="editViewContainer" id="menumanager">
		<div>
			<div style="padding-top:10px; padding-left:10px;padding-bottom: 15px;">
			{vtranslate('LBL_ADDICON', $MODULENAME)}:&nbsp;&nbsp; 
			<input id="paintedicon" name="paintedicon" type="checkbox" {if $PAINTEDICON eq 1} checked="checked"  {/if} />
                <button class="btn addButton pull-right" style="margin-right:27px;" onclick="openBlockEdition();">
                    <i class="icon-plus icon-white" title="{vtranslate('LBL_ADDNEWMENU', $MODULENAME)}"></i> 
                    <strong>{vtranslate('LBL_ADDBLOCK', $MODULENAME)}</strong>
                </button> 
			</div>
		</div>
		<div id="pre-sortable-box"></div>
		<div id="sortable-box" class="  row-fluid">
			<table class="table">
			<tr >
			<td class="span12  row-fluid">
			{$w=0}
			{$licznik=5}
			{foreach from=$MENUSTRUKTURE.group item=group_item key=group_key }
			{if $w eq 4}
				</td>
				</tr>
				<tr >
				<td class="span12 row-fluid">
				{$w=0}
		<div style="display:none">	{$licznik++} </div>
			{/if}
				<div class="span3 blockSortable border1px marginBottom10px" data-group-id="{$group_item.id}" data-group-seq="{$group_key}" data-group-licznik="{$licznik}" style="height:auto">
					<div class="row-fluid layoutBlockHeader">
						<div class="blockLabel span8 padding10 marginLeftZero 
                            {if $group_item.visible eq 0}
                                 not_visible
                            {elseif $group_item.permission eq 'no'}
                                 no_permission
                            {/if}
                            "><img class="alignMiddle" src="{vimage_path('drag.png')}" />&nbsp;&nbsp; 	
                            {if !empty($group_item.locationicon)}
							<img style="vertical-align: middle; max-width: {$group_item.iconf}px; max-height:{$group_item.icons}px" src="{$group_item.locationicon}" alt="{$group_item.locationicon}"/>&nbsp;&nbsp; 
                            {/if}
                            {if $group_item.permission eq 'no'}
                                <span title="{vtranslate('LBL_NOPERMISSION', $MODULENAME)}">{$group_item.label}</span>
                            {elseif $group_item.visible eq 0}
                                <span title="{vtranslate('LBL_INVISIBLE', $MODULENAME)}">{$group_item.label}</span>
                            {else}
                             {$group_item.label} 
								
                            {/if}
			
                        </div>
						<div class="marginLeftZero span3">
                            <div class="pull-right btn-toolbar blockActions" style="margin-left:10px;">
                                <a href="javascript:openBlockEdition({$group_item.id})" class="">
                                    <i class="icon-wrench" title="{vtranslate('LBL_EDYTUJBLOK', $MODULENAME)}"></i>
                                </a>
                            </div>
                            <div class="pull-right btn-toolbar blockActions">
                                <a href="javascript:openMenuCreation({$group_item.id})" class="">
                                    <i class="icon-plus-sign" title="{vtranslate('LBL_ADDNEWMENU', $MODULENAME)}"></i>
                                </a>
                            </div>
                        </div>
					</div>
                    {if count($MENUSTRUKTURE.menu[$group_item.id]) gt 0}
                        <div class="editFieldsTable connectedSortable block-{$group_item.id}" style="min-height:50px; padding:5px;">
                        {foreach from=$MENUSTRUKTURE.menu[$group_item.id] item=menu_item key=menu_key}
                            <div class="row-fluid marginLeftZero border1px fieldSortable block_{$group_item.id}" data-group-id="{$group_item.id}" data-fields-id="{$menu_item.id}" data-fields-seq="{$menu_key}">
                                <span class="span1 drag-icon"><img src="{vimage_path('drag.png')}" border="0" title="x"/></span>
                            
														
								<div class="span9 marginLeftZero paddingTop3 
                                    {if $menu_item.visible eq 0}
                                         not_visible
                                    {elseif $menu_item.permission eq 'no'}
                                         no_permission
                                    {/if}">


									
                                    {if $menu_item.label eq '*separator*'}
									
			{* <!--				<img style="vertical-align: middle;padding-top: 1%; float:left;max-width: {$menu_item.iconf}px; max-height:{$menu_item.icons}px"  src="{$menu_item.locationicon}" alt="{$menu_item.locationicon}"/> -->*}
                            	    <div style="height:5px;">&nbsp;</div>
                                  <hr class="style-one" title="{vtranslate('LBL_SEPARATOR', $MODULENAME)}" /> 
									
                                    {elseif $menu_item.visible eq 0}
                                        <span title="{vtranslate('LBL_INVISIBLE', $MODULENAME)}">
	{if !empty($menu_item.locationicon)}
    <img style="vertical-align: middle; max-width: {$menu_item.iconf}px; max-height:{$menu_item.icons}px" src="{$menu_item.locationicon}" alt="{$menu_item.locationicon}"/>
    {/if}&nbsp; 									
										{$menu_item.label}</span>
                                    {elseif $menu_item.permission eq 'no'}
                                        <span title="{vtranslate('LBL_NOPERMISSION', $MODULENAME)}">
	{if !empty($menu_item.locationicon)}
    <img style="vertical-align: middle; max-width: {$menu_item.iconf}px; max-height:{$menu_item.icons}px" src="{$menu_item.locationicon}" alt="{$menu_item.locationicon}"/>
    {/if}&nbsp; 									
										{$menu_item.label}</span>
                                    {else}
                                        {if !empty($menu_item.locationicon)}
                                            <img style="vertical-align: middle; max-width: {$menu_item.iconf}px; max-height:{$menu_item.icons}px" src="{$menu_item.locationicon}" alt="{$menu_item.locationicon}"/>
                                        {/if}
                                        &nbsp;  {$menu_item.label}
                                    {/if}
          
                                </div>
                                <div class="span1 marginLeftZero ">
                                    {if $menu_item.new_window eq 1}
                                        <i class="icon-share alignMiddle" title="{vtranslate('LBL_NEWWINDOW', $MODULENAME)}"></i>
									{else}
										&nbsp;
                                    {/if}
                                </div>
								<div class="span1 marginLeftZero paddingTop3">     
                                   {if $menu_item.type ne '3'}
									<a href="javascript:openLangEdition({$menu_item.id})" > <i class="icon-list-alt " title="{vtranslate('LBL_LANGEDITION', $MODULENAME)}"></i></a>
									{/if}
                                </div>
                                <div class="span1 pull-right marginLeftZero paddingTop3 ">     
                                    <a href="javascript:openMenuEdition({$menu_item.id})"><i class="icon-wrench " title="{vtranslate('LBL_EDITMENU', $MODULENAME)}"></i></a>
                                </div>
								
                            </div>
                        {/foreach}                    
                        </div>
                    {else}
                        <div class="editFieldsTable connectedSortable block-{$group_item.id}" style="min-height:50px; padding:5px;"></div>
                    {/if}
				</div>
			<div style="display:none">	{$w++} </div>
			{/foreach}
			</td>
			</tr>
			</table>
		</div>
    </div>
</div>
<script>
	jQuery('[name="paintedicon"]').change(function(){
		var paintedicon = ((jQuery(this).is(":checked")) ? 1 : 0);
		var params = {};
			params.async = false;
			params.dataType = 'json';
			params.data = { 
				'module' : "OSSMenuManager",
				'action' : "SaveIcon",
				'paintedicon' : paintedicon,
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
					location.reload();
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
			}
		);
	});
</script>