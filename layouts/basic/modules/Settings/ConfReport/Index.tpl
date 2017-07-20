{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}

<div class="">
	<div class="widget_header row">
		<div class="col-xs-10">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{App\Language::translate('LBL_CONFREPORT_DESCRIPTION', $MODULE)}
		</div>
		<div class="col-xs-2">
			{*<!--
			<button class="btn btn-primary testSpeed pull-right">
			<span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span>&nbsp;&nbsp;
			{App\Language::translate('BTN_SERVER_SPEED_TEST',$QUALIFIED_MODULE)}
			</button>
			-->*}
		</div>
	</div>
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#Configuration">{App\Language::translate('LBL_YETIFORCE_ENGINE', $MODULE)}</a></li>
        <li><a data-toggle="tab" href="#Permissions">{App\Language::translate('LBL_FILES_PERMISSIONS', $MODULE)}</a></li>
			{if \App\Module::isModuleActive('OSSMail')}
			<li><a href="#check_config" data-toggle="tab">{App\Language::translate('LBL_CHECK_CONFIG', $MODULE)}</a></li>
			{/if}
    </ul>
    <div class="tab-content">
        <div id="Configuration" class="tab-pane fade in active">
			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_LIBRARY', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_INSTALLED', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_MANDATORY', $MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=Settings_ConfReport_Module_Model::getConfigurationLibrary() key=key item=item}
						<tr {if $item.status == 'LBL_NO'}class="danger"{/if}>
							<td>
								<label>{App\Language::translate($key,$MODULE)}</label>
								{if isset($item.help) && $item.status}<a href="#" class="popoverTooltip pull-right" data-trigger="focus" data-placement="rigth" data-content="{App\Language::translate($item.help, $MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>{/if}
							</td>
							<td><label>{App\Language::translate($item.status, $MODULE)}</label></td>
							<td><label>
									{if $item.mandatory}
										{App\Language::translate('LBL_MANDATORY', $MODULE)}
									{else}
										{App\Language::translate('LBL_OPTIONAL', $MODULE)}
									{/if}
								</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<br />
			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="3" class="mediumWidthType">
							<span>{App\Language::translate('LBL_SYSTEM_STABILITY', $MODULE)}</span>
						</th>
					</tr>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_PARAMETER', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_RECOMMENDED', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_VALUE', $MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=Settings_ConfReport_Module_Model::getStabilityConf() key=key item=item}
						<tr {if $item.status}class="danger"{/if}>
							<td>
								<label>{$key}</label>
								{if isset($item.help) && $item.status}<a href="#" class="popoverTooltip pull-right" data-trigger="focus" data-placement="rigth" data-content="{App\Language::translate($item.help, $MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>{/if}
							</td>
							<td><label>{App\Language::translate($item.prefer, $MODULE)}</label></td>
							<td><label>{App\Language::translate($item.current, $MODULE)}</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<br />
			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="3" class="mediumWidthType">
							<span>{App\Language::translate('LBL_SYSTEM_SECURITY', $MODULE)}</span>
						</th>
					</tr>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_PARAMETER', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_RECOMMENDED', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_VALUE', $MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=Settings_ConfReport_Module_Model::getSecurityConf() key=key item=item}
						<tr {if $item.status}class="danger"{/if}>
							<td>
								<label>{$key}</label>
								{if isset($item.help) && $item.status}<a href="#" class="popoverTooltip pull-right" data-trigger="focus" data-placement="rigth" data-content="{App\Language::translate($item.help, $MODULE)}"><i class="glyphicon glyphicon-info-sign"></i></a>{/if}
							</td>
							<td><label>{App\Language::translate($item.prefer, $MODULE)}</label></td>
							<td><label>{App\Language::translate($item.current, $MODULE)}</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<br />
			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="2" class="mediumWidthType">
							{App\Language::translate('LBL_ENVIRONMENTAL_INFORMATION', $MODULE)}
						</th>
					</tr>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_PARAMETER', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_VALUE', $MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=Settings_ConfReport_Module_Model::getSystemInfo() key=key item=item}
						<tr>
							<td><label>{App\Language::translate($key, $MODULE)}</label></td>
							<td><label>{$item}</label></td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<br />
        </div>
        <div id="Permissions" class="tab-pane fade">
			<table class="table tableRWD table-bordered table-condensed themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_FILE', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_PATH', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_PERMISSION', $MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=Settings_ConfReport_Module_Model::getPermissionsFiles() key=key item=item}
						<tr {if $item.permission eq 'FailedPermission'}class="danger"{/if}>
							<td width="23%"><label class="marginRight5px">{App\Language::translate($key, $MODULE)}</label></td>
							<td width="23%"><label class="marginRight5px">{App\Language::translate($item.path, $MODULE)}</label></td>
							<td width="23%"><label class="marginRight5px">
									{if $item.permission eq 'FailedPermission'}
										{App\Language::translate('LBL_FAILED_PERMISSION', $MODULE)}
									{else}
										{App\Language::translate('LBL_TRUE_PERMISSION', $MODULE)}
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
				<iframe id="roundcube_interface" style="width: 100%; min-height: 590px;" src="index.php?module=OSSMail&view=CheckConfig" frameborder="0"> </iframe>
			</div>
		{/if}
    </div>
</div>
