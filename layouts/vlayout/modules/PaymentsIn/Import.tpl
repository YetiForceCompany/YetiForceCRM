{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<style>
.verticalText{
	line-height:28px
}
</style>
<div style="padding:20px">	  
   <h3 class="span8 ">{vtranslate('Import', $MODULENAME)}</h3>
</div>

<div class="container" style="margin-top:20px">
	<form method="POST" action="index.php?module=PaymentsIn&view=step1" name="ical_import"  enctype="multipart/form-data">
		<div class="row-fluid" >
			<div class="span12">

				<div class="row-fluid" >
					<div class="span6 " style="text-align:center ">
						<div class="alert alert-info">
							{vtranslate('Import wyciągów bankowych', $MODULENAME)}
						</div>
					</div>
					<div class="span5 well">
						<div class="row-fluid" >
							<div class="span2 verticalText" >
								{vtranslate('Typ', $MODULENAME)}
							</div>
							<div class="span10" >
								<select  name="type"  >
									{foreach from=$TYP item=item}
										<option value="{$item}">{vtranslate({$item}, $MODULENAME)}</option>
									{/foreach}	
								</select>
							</div>
						</div>	
						<div class="row-fluid" >	
							<div class="span2 verticalText">
								{vtranslate('Bank', $MODULENAME)}
							</div>
							<div class="span10">
								<select   name="bank" >
									{foreach from=$BANK item=item}
										<option value="{$item}">{vtranslate({$item}, $MODULENAME)}</option>
									{/foreach}
								</select>
							</div>
							
						</div>	
						<div class="row-fluid" >	
							{*<div class="span2 verticalText" >
								{vtranslate('Plik', $MODULENAME)}
							</div>
							<div class="span10">
							</div>
							*}
								<input name="file" type="file" class="filestyle" data-input="false" style="margin-left:68px">
							
						</div>
					</div>	
				</div>
			</div>
		</div>
	{*
                    <td class="" colspan="5">
							<table class="table ">
									<tr>
										<!--<th class="" colspan="4" style="color:black">{vtranslate('Delete_panel', $MODULENAME)}{$MODULENAME}</th>-->
									</tr>
									<tr>
										<td class="" colspan="1">
											{vtranslate('Typ', $MODULENAME)}
										</td>
										<td class="" colspan="4">
											<select style=" margin-bottom:0px" name="type"  >
												{foreach from=$TYP item=item}
													<option value="{$item}">{vtranslate({$item}, $MODULENAME)}</option>
												{/foreach}	
											</select>
										</td>
									</tr>  
									<tr>
										<td class="" colspan="1">
											{vtranslate('Bank', $MODULENAME)}
										</td>
										<td class="" colspan="4">
											<select style=" margin-bottom:0px"  name="bank" >
												{foreach from=$BANK item=item}
													<option value="{$item}">{vtranslate({$item}, $MODULENAME)}</option>
												{/foreach}
											</select>
										</td>
									</tr> 
								
									<tr>
										<td class="" colspan="1">
											{vtranslate('Plik', $MODULENAME)}
										</td>
										<td class="" colspan="6">
											<input name="file" type="file" accept="text/plain"  class="filestyle" data-input="false">
										</td>
									</tr> 
							</table>            
                    </td>
                </tr>      
				
			</tbody>
        </table>    
*}
       <div class="pull-right" style="margin-top:20px; margin-right:40px">
                      <button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('NEXT', $MODULE)}</strong></button>
       </div>
    </form>
</div>
{literal}
<script>
function PaymentsIn() {

             this.preSave = function() {
                var thisInstance = this;
			
                jQuery(':submit').on('click', function() {
                    var file = jQuery('input[type="file"]').val();
                    if(file == ""){
  							var msg = '{/literal}{vtranslate("LBL_ERROR_FILE", 'PaymentsIn')}{literal}';
								Vtiger_Helper_Js.showPnotify(msg);
							return false;
					}else {
						var type = file.split('.');
					//	console.log(type);
						var id = type.length;
					//	console.log(id);
						if(type[id-1]!='txt' && type[id-1]!='sta'){
							var msg = '{/literal}{vtranslate("LBL_ERROR_TYPE", 'PaymentsIn')}{literal}';
								Vtiger_Helper_Js.showPnotify(msg);
							return false;
						}
							
					}
                  
                })
            },
			
            this.registerEvents = function() {
                var thisInstance = this;
				thisInstance.preSave();
            };
}


jQuery(document).ready(function() {
    var dc = new PaymentsIn();
    dc.registerEvents();
})
</script>
{/literal}	
	

	