{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}

<div class="">
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate('LBL_CONFREPORT_DESCRIPTION', $MODULE)}
		</div>
	</div>
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#Configuration">{vtranslate('LBL_YETIFORCE_ENGINE', $MODULE)}</a></li>
        <li><a data-toggle="tab" href="#Permissions">{vtranslate('LBL_FILES_PERMISSIONS', $MODULE)}</a></li>
			{if \App\Module::isModuleActive('OSSMail')}
			<li><a href="#check_config" data-toggle="tab">{vtranslate('LBL_CHECK_CONFIG', $MODULE)}</a></li>
			{/if}
    </ul>
    <div class="tab-content">
        <div id="Configuration" class="tab-pane fade in active">
			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_LIBRARY', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_INSTALLED', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_MANDATORY', $MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=Settings_ConfReport_Module_Model::getConfigurationLibrary() key=key item=item}
						<tr {if $item.status == 'LBL_NO'}class="danger"{/if}>
							<td><label>{vtranslate($key, $MODULE)}</label></td>
							<td><label>{vtranslate($item.status, $MODULE)}</label></td>
							<td><label>
									{if $item.mandatory}
										{vtranslate('LBL_MANDATORY', $MODULE)}
									{else}
										{vtranslate('LBL_OPTIONAL', $MODULE)}
									{/if}
								</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<br/>
			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable">
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
					{foreach from=Settings_ConfReport_Module_Model::getConfigurationValue() key=key item=item}
						<tr {if $item.status}class="danger"{/if}>
							<td><label>{$key}</label></td>
							<td><label>{vtranslate($item.prefer, $MODULE)}</label></td>
							<td><label>{vtranslate($item.current, $MODULE)}</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<br/>
			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_PARAMETER', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{vtranslate('LBL_VALUE', $MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=Settings_ConfReport_Module_Model::getSystemInfo() key=key item=item}
						<tr>
							<td><label>{vtranslate($key, $MODULE)}</label></td>
							<td><label>{$item}</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<br/>
        </div>
        <div id="Permissions" class="tab-pane fade">
			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th class="mediumWidthType">
							<span>{vtranslate('LBL_FILE', $MODULE)}</span>
						</th>
						<th class="mediumWidthType">
							<span>{vtranslate('LBL_SPACE', $MODULE)}</span>
						</th> 											
					</tr>
				</thead>
				<tbody>
					{foreach from=vtlib\Functions::getDiskSpace() key=key item=item}			
						<tr>
							<td>{vtranslate('LBL_SPACE_'|cat:strtoupper($key), $MODULE)}</td>
							<td>{vtlib\Functions::showBytes($item)}</td>			
						</tr>
					{/foreach}
				</tbody>
			</table>
			<br/>

			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable">
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
					{foreach from=Settings_ConfReport_Module_Model::getPermissionsFiles() key=key item=item}			
						<tr {if $item.permission eq 'FailedPermission'}class="danger"{/if}>
							<td width="23%"><label class="marginRight5px">{vtranslate($key, $MODULE)}</label></td>
							<td width="23%"><label class="marginRight5px">{vtranslate($item.path, $MODULE)}</label></td>
							<td width="23%"><label class="marginRight5px">
									{if $item.permission eq 'FailedPermission'}
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
		{if \App\Module::isModuleActive('OSSMail')}
			<div class='editViewContainer tab-pane' id="check_config">
				<iframe id="roundcube_interface" style="width: 100%; min-height: 590px;" src="{$CCURL}" frameborder="0"> </iframe>		
			</div>
		{/if}
    </div>
</div>
