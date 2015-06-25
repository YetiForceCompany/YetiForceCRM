<div class="" style="margin-top:10px;">
	<h3>{vtranslate('LBL_CONFIGURATION', $MODULE)}</h3>&nbsp;{vtranslate('LBL_CONFREPORT_DESCRIPTION', $MODULE)}<hr>
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#Configuration">{vtranslate('LBL_YETIFORCE_ENGINE', $MODULE)}</a></li>
        <li><a data-toggle="tab" href="#Permissions">{vtranslate('LBL_FILES_PERMISSIONS', $MODULE)}</a></li>
		{if vtlib_isModuleActive('OSSMail')}
			<li><a href="#check_config" data-toggle="tab">{vtranslate('LBL_CHECK_CONFIG', $MODULE)}</a></li>
		{/if}
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
							<span>{vtranslate('LBL_RECOMMENDED', $MODULE)}</span>
						</th>  	
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_VALUE', $MODULE)}</span>
						</th> 
					</tr>
				</thead>
				<tbody>
					{foreach from=Settings_ConfReport_Module_Model::getConfigurationValue() key=key item=foo}
						<tr {if $foo.status}class="danger"{/if}>
							<td><label>{$key}</label></td>
							<td><label>{vtranslate($foo.prefer, $MODULE)}</label></td>
							<td><label>{vtranslate($foo.current, $MODULE)}</label></td>
						</tr>
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
					{foreach from=Settings_ConfReport_Module_Model::getPermissionsFiles() key=key item=foo}			
						<tr {if $foo.permission eq 'FailedPermission'}class="error"{/if}>
							<td width="23%"><label class="marginRight5px">{vtranslate($key, $MODULE)}</label></td>
							<td width="23%"><label class="marginRight5px">{vtranslate($foo.path, $MODULE)}</label></td>
							<td width="23%"><label class="marginRight5px">
									{if $foo.permission eq 'FailedPermission'}
										{vtranslate('LBL_FAILED_PERMISSION', $MODULE)}
									{else}
										{vtranslate('LBL_TRUE_PERMISSION', $MODULE)}
									{/if}
								</label></td>			
						</tr>
					{/foreach}
				</tbody>
			</table>
        </div>
		{* check config module *}
		{if vtlib_isModuleActive('OSSMail')}
			<div class='editViewContainer tab-pane' id="check_config">
				<iframe id="roundcube_interface" style="width: 100%; min-height: 590px;" src="{$CCURL}" frameborder="0"> </iframe>		
			</div>
		{/if}
    </div>
</div>
