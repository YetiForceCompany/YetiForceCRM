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
<h3 id="myModalLabel">{vtranslate('LBL_NAME', 'OSSMenuManager')}</h3>
</div>



	<div class="modal-body" style="width: auto;">	
	
		<div class="modal-Fields">
	
		<div id="langfield_div" >
<table>		
<tr valign="top" style="padding-left: 10px;">		
		{$i=1}
<td style="width:auto;">		
			{foreach from=$LANG item=item key=key}
{if $i eq '6'}
</td>
<td style="padding-left: 10px; width:auto;">
{$i=1}
{/if}
			{$q=0}
			{foreach from=$LANGV item=item2 key=key2}
			
			{if $key eq $key2}
			<div class="row-fluid">
				<div class="span5" style=" width:auto;"><div style="display:none">{$i++}. </div>{$item.label}</div>	<br />
				<input class="translang" style="max-height: 20px;  width: 140px; padding: 0px; font-size: 90%;" type="text" name="{$key}" placeholder="{$item.label}" value="{$item2}" /><br />
			</div>
				{$q=1}
			{/if}
			{/foreach}
			{if $q!=1}	
			<div class="row-fluid">
				<div class="span5" style=" width:auto;"><div style="display:none">{$i++}. </div> {$item.label}</div><br />	
				<input class="translang" style="max-height: 20px;  width: 140px; padding: 0px; font-size: 90%;" type="text" name="{$key}" placeholder="{$item.label}"  /><br />
			</div>
			{/if}

	
	
			{/foreach}
</td style="padding-right: 10px;">	
</tr>
</table>
			
		</div>
		</div>
	</div>	




	

<div class="modal-footer">
<button class="btn" id="closeModal" data-dismiss="modal" aria-hidden="true">{vtranslate('LBL_CLOSE', 'OSSMenuManager')}</button>
<button class="btn addButton" onclick="editLang();" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SAVE', 'OSSMenuManager')}</button>
</div>

{literal}
<script>
function editLang() {
   var menuId = '{/literal}{$MENUID}{literal}';
    var translang= Array();
	var i=0;
	
	jQuery('.translang').each(function(){
   // console.log(jQuery(this).val());
	if(jQuery(this).val() != '')
	{
	translang[i]=jQuery(this).attr('name')+'*'+jQuery(this).val();
	i++;	}
	}); 
	translang=translang.join('#');
//	console.log(translang);
	
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
                'action' : "EditLang",
                'id' : menuId,
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

</script>
{/literal}