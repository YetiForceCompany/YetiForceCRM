<style>
.confTable td, label, span{
    text-align:center !important; 
    vertical-align:middle !important; 
}
.table tbody tr.error > td {
	background-color: #f2dede;
}
</style>
<div class="container-fluid" style="margin-top:10px;">
	<h3>{vtranslate('LBL_CONFIGURATION', $MODULE)}</h3>&nbsp;{vtranslate('LBL_CONFREPORT_DESCRIPTION', $MODULE)}<hr>
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#Configuration">{vtranslate('LBL_YETIFORCE_ENGINE', $MODULE)}</a></li>
        <li><a data-toggle="tab" href="#Permissions">{vtranslate('LBL_FILES_PERMISSIONS', $MODULE)}</a></li>
		<li><a href="#check_config" data-toggle="tab">{vtranslate('LBL_CHECK_CONFIG', $MODULE)}</a></li>
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
						{if is_array($foo) eq 1 && isset($foo.current)}
							<tr class="error">
								<td><label class="marginRight5px">{$key}</label></td>
								<td><label class="marginRight5px">{vtranslate($foo.current, $MODULE)}</label></td>
								<td><label class="marginRight5px">{vtranslate($foo.prefer, $MODULE)}</label></td>
							</tr>
						{elseif is_array($foo) neq 1}
							<tr>
								<td><label class="marginRight5px">{$key}</label></td>
								<td colspan="2"><label class="marginRight5px">{vtranslate($foo, $MODULE)}</label></td>
							</tr>
						{else}
							<tr>
								<td><label class="marginRight5px">{$key}</label></td>
								<td><label class="marginRight5px">{vtranslate($foo.prefer, $MODULE)}</label></td>
								<td><label class="marginRight5px">{vtranslate($foo.prefer, $MODULE)}</label></td>
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
							<tr class="error">
								<td width="23%"><label class="marginRight5px">{vtranslate($key, $MODULE)}</label></td>
								<td width="23%"><label class="marginRight5px">{vtranslate($foo.path, $MODULE)}</label></td>
								<td width="23%"><label class="marginRight5px">{vtranslate('LBL_FAILED_PERMISSION', $MODULE)}</label></td>			
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
		{* check config module *}
		<div class='editViewContainer tab-pane' id="check_config">
			<iframe id="roundcube_interface" style="width: 100%; min-height: 590px;" src="{$CCURL}" frameborder="0"> </iframe>		
		</div>
    </div>
</div>