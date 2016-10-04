{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}

<div>	  
    <h4>{vtranslate('Import', $MODULENAME)}</h4>
</div>

<div class="">
	<form method="POST" action="index.php?module=PaymentsOut&view=step1" name="ical_import"  enctype="multipart/form-data">
		<div class="row" >
			<div class="col-md-12">

				<div class="row" >
					<div class="col-md-6 ">
						<div class="alert alert-info">
							{vtranslate('Informacje', $MODULENAME)}	
						</div>
					</div>
					<div class="col-md-5 well form-horizontal">
						<div class="form-group" >
							<label class="col-md-2 control-label" >
								{vtranslate('Typ', $MODULENAME)}
							</label>
							<div class="col-md-10" >
								<select  name="type"  class="chzn-select form-control" >
									{foreach from=$TYP item=item}
										<option value="{$item}">{vtranslate({$item}, $MODULENAME)}</option>
									{/foreach}	
								</select>
							</div>
						</div>	
						<div class="form-group" >	
							<label class="col-md-2 control-label">
								{vtranslate('Bank', $MODULENAME)}
							</label>
							<div class="col-md-10">
								<select class="form-control chzn-select" name="bank" >
									{foreach from=$BANK item=item}
										<option value="{$item}">{vtranslate({$item}, $MODULENAME)}</option>
									{/foreach}
								</select>
							</div>
							
						</div>
						<div class="row">	
							<div class="col-md-2" >
							</div>
							<div class="col-md-10">
								<input name="file" type="file" class="" data-input="false">
							</div>
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
      <div class="col-md-11 paddingRightZero">
			<button class="btn pull-right btn-success" type="submit" name="saveButton"><strong>{vtranslate('NEXT', $MODULE)}</strong></button>
       </div>
    </form>
</div>
{literal}
<script>
function PaymentsOut() {

             this.preSave = function() {
                var thisInstance = this;
			
                jQuery(':submit').on('click', function() {
                    var file = jQuery('input[type="file"]').val();
                    if(file == ""){
  							var msg = '{/literal}{vtranslate("LBL_ERROR_FILE", 'PaymentsOut')}{literal}';
								Vtiger_Helper_Js.showPnotify(msg);
							return false;
					}else {
						var type = file.split('.');
						var id = type.length;
						if(type[id-1]!='txt'){
							var msg = '{/literal}{vtranslate("LBL_ERROR_TYPE", 'PaymentsOut')}{literal}';
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
    var dc = new PaymentsOut();
    dc.registerEvents();
})
</script>
{/literal}	
	

	
