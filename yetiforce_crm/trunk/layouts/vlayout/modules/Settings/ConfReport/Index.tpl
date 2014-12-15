<style>
.confTable td, label, span{
    text-align:center !important; 
    vertical-align:middle !important; 
}
</style>
<div class="container-fluid" style="margin-top:10px;">
	<h3>{vtranslate('LBL_CONFIGURATION', $MODULE)}</h3>&nbsp;{vtranslate('LBL_CONFREPORT_DESCRIPTION', $MODULE)}<hr>
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#Configuration">{vtranslate('LBL_CONFIGURATION', $MODULE)}</a></li>
        <li><a data-toggle="tab" href="#Permissions">{vtranslate('LBL_FILES_PERMISSIONS', $MODULE)}</a></li>
    </ul>
    <div class="tab-content">
        <div id="Configuration" class="tab-pane fade in active">
			<table class="table table-bordered table-condensed themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_PARAMETER', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_VALUE', $MODULE)}</span>
						</th> 
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_RECOMMENDED', $MODULE)}</span>
						</th>  				
						</tr>
				</thead>
				<tbody>
					{foreach from=$CONF key=key item=foo}	
						{if isset($foo.current)}
							<tr style="background-color: #FFBABA;">
								<td width="23%"><label class="marginRight5px" style="color: #D8000C;">{$key}</label></td>
								<td width="23%"><label class="marginRight5px" style="color: #D8000C;">{vtranslate($foo.current, $MODULE)}</label></td>
								<td width="23%"><label class="marginRight5px" style="color: #D8000C;">{vtranslate($foo.prefer, $MODULE)}</label></td>
							</tr>
						{else}			
							<tr>	
								<td width="23%"><label class="marginRight5px">{$key}</label></td>
								<td width="23%"><label class="marginRight5px">{vtranslate($foo.prefer, $MODULE)}</label></td>
								<td width="23%"><label class="marginRight5px">{vtranslate($foo.prefer, $MODULE)}</label></td>
							</tr>
						{/if}	
					{/foreach}
				</tbody>
			</table>
        </div>
        <div id="Permissions" class="tab-pane fade">
 			<table class="table table-bordered table-condensed themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_FILE', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_PATH', $MODULE)}</span>
						</th> 							
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_PERMISSION', $MODULE)}</span>
						</th>  				
					</tr>
				</thead>
				<tbody>
					{foreach from=$PERMISSIONS key=key item=foo}	
						{if $foo.permission eq 'FailedPermission'}
							<tr style="background-color: #FFBABA;">
								<td width="23%"><label class="marginRight5px" style="color: #D8000C;">{vtranslate($key, $MODULE)}</label></td>
								<td width="23%"><label class="marginRight5px" style="color: #D8000C;">{vtranslate($foo.path, $MODULE)}</label></td>
								<td width="23%"><label class="marginRight5px" style="color: #D8000C;">{vtranslate('LBL_FAILED_PERMISSION', $MODULE)}</label></td>							
							</tr>
						{else}			
							<tr>	
								<td width="23%"><label class="marginRight5px">{vtranslate($key, $MODULE)}</label></td>
								<td width="23%"><label class="marginRight5px">{vtranslate($foo.path, $MODULE)}</label></td>
								<td width="23%"><label class="marginRight5px">{vtranslate('LBL_TRUE_PERMISSION', $MODULE)}</label></td>
							</tr>
						{/if}	
					{/foreach}
				</tbody>
			</table>
        </div>
    </div>
</div>