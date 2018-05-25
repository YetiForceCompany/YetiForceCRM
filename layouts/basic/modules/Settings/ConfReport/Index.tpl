{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}

<div class="tpl-Settings-ConfReport-Index">
	<div class="widget_header row">
		<div class="col-10">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
		</div>
		<div class="col-2 align-items-center d-flex justify-content-end">
			{*<button class="btn btn-primary testSpeed float-right">*}
			{*<span class="fab fa-cloudscale"></span>*}
			{*{App\Language::translate('BTN_SERVER_SPEED_TEST',$QUALIFIED_MODULE)}*}
			{*</button>*}
		</div>
	</div>
	<div class="badge badge-info my-2">
		<a> {App\Language::translate('LBL_CONFREPORT_DESCRIPTION', $MODULE)}</a>
	</div>
	<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link active" data-toggle="tab"
								href="#Configuration">{App\Language::translate('LBL_YETIFORCE_ENGINE', $MODULE)}</a>
		</li>
		<li class="nav-item"><a class="nav-link" data-toggle="tab"
								href="#Permissions">{App\Language::translate('LBL_FILES_PERMISSIONS', $MODULE)}</a></li>
	</ul>
	<div class="tab-content">
		<div id="Configuration" class="tab-pane fade in active show">
			<div class="form-row">
				<div class="col-md-4">
					<table class="table table-bordered table-sm u-word-break-all">
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
						{foreach from=Settings_ConfReport_Module_Model::getLibrary() key=key item=item}
							<tr {if $item.status == 'LBL_NO'}class="table-danger"{/if}>
								<td>
									<label class="u-text-small-bold">{App\Language::translate($key,$MODULE)}</label>
									{if isset($item.help) && $item.status}<a href="#"
																			 class="js-popover-tooltip float-right"
																			 data-js="popover" data-trigger="focus"
																			 data-placement="right"
																			 data-content="{App\Language::translate($item.help, $MODULE)}">
											<span class="fas fa-info-circle"></span></a>{/if}
								</td>
								<td>
									<label class="u-text-small-bold">{App\Language::translate($item.status, $MODULE)}</label>
								</td>
								<td><label class="u-text-small-bold">
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
					<br/>
					<table class="table table-bordered table-sm u-word-break-all">
						<thead>
						<tr class="blockHeader">
							<th colspan="3" class="mediumWidthType">
								{App\Language::translate('LBL_DATABASE_INFORMATION', $MODULE)}
							</th>
						</tr>
						<tr class="blockHeader">
							<th colspan="1" class="mediumWidthType w-25">
								<span>{App\Language::translate('LBL_PARAMETER', $MODULE)}</span>
							</th>
							<th colspan="1" class="mediumWidthType w-25">
								<span>{App\Language::translate('LBL_RECOMMENDED', $MODULE)}</span>
							</th>
							<th colspan="1" class="mediumWidthType">
								<span>{App\Language::translate('LBL_VALUE', $MODULE)}</span>
							</th>
						</tr>
						</thead>
						<tbody>
						{foreach from=$DB_CONF key=key item=item}
							<tr {if $item['status']}class="table-danger"{/if}>
								<td>
									<label class="u-text-small-bold">{App\Language::translate($key, $MODULE)}</label>
									{if isset($item.help) && $item.status}<a href="#"
																			 class="js-popover-tooltip float-right"
																			 data-js="popover" data-trigger="focus"
																			 data-placement="right"
																			 data-content="{\App\Language::translateEncodeHtml($item.help, $MODULE)}">
											<span class="fas fa-info-circle"></span></a>{/if}
								</td>
								{if $item['recommended'] === false}
									<td colspan="2"><label class="u-text-small-bold">{$item['current']}</label></td>
								{else}
									<td><label class="u-text-small-bold">{$item['recommended']}</label></td>
									<td><label class="u-text-small-bold">{$item['current']}</label></td>
								{/if}
							</tr>
						{/foreach}
						</tbody>
					</table>
				</div>
				<div class="col-md-4">
					<table class="table table-bordered table-sm u-word-break-all">
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
						{foreach from=$SECURITY_CONF key=key item=item}
							<tr {if $item.status}class="table-danger"{/if}>
								<td>
									<label class="u-text-small-bold">{$key}</label>
									{if isset($item.help) && $item.status}
										<a href="#"
										   class="js-popover-tooltip float-right"
										   data-js="popover" data-trigger="focus"
										   data-placement="right"
										   data-content="{\App\Language::translateEncodeHtml($item.help, $MODULE)}">
											<span class="fas fa-info-circle"></span>
										</a>
									{/if}
								</td>
								<td>
									<label class="u-text-small-bold">{App\Language::translate($item.recommended, $MODULE)}</label>
								</td>
								<td>
									<label class="u-text-small-bold">{App\Language::translate($item.current, $MODULE)}</label>
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
					<br/>
					<table class="table table-bordered table-sm u-word-break-all">
						<thead>
						<tr class="blockHeader">
							<th colspan="3" class="mediumWidthType">
								{App\Language::translate('LBL_ENVIRONMENTAL_INFORMATION', $MODULE)}
							</th>
						</tr>
						<tr class="blockHeader">
							<th colspan="1" class="mediumWidthType w-25">
								<span>{App\Language::translate('LBL_PARAMETER', $MODULE)}</span>
							</th>
							<th colspan="1" class="mediumWidthType">
								<span>{App\Language::translate('LBL_WWW_VALUE', $MODULE)}</span>
							</th>
							<th colspan="1" class="mediumWidthType">
								<span>{App\Language::translate('LBL_CLI_VALUE', $MODULE)}</span>
							</th>
						</tr>
						</thead>
						<tbody>
						{foreach from=$SYSTEM_INFO key=key item=item}
							<tr>
								<td>
									<label class="u-text-small-bold">{App\Language::translate($key, $MODULE)}</label>
								</td>
								{if is_array($item)}
									<td>
										<label class="u-text-small-bold">{App\Language::translate($item['www'], $MODULE)}</label>
									</td>
									<td>
										<label class="u-text-small-bold">{App\Language::translate($item['cli'], $MODULE)}</label>
									</td>
								{else}
									<td colspan="2">
										<label class="u-text-small-bold">{$item}</label>
									</td>
								{/if}
							</tr>
						{/foreach}
						</tbody>
					</table>
				</div>
				<div class="col-md-4">
					<table class="table table-bordered table-sm">
						<thead>
						<tr class="blockHeader">
							<th colspan="4" class="mediumWidthType">
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
								<span>{App\Language::translate('LBL_WWW_VALUE', $MODULE)}</span>
							</th>
							<th colspan="1" class="mediumWidthType">
								<span>{App\Language::translate('LBL_CLI_VALUE', $MODULE)}</span>
							</th>
						</tr>
						</thead>
						<tbody>
						{foreach from=$STABILITY_CONF key=key item=item}
							<tr {if $item['incorrect']}class="table-danger"{/if}>
								<td class="u-w-5per">
									<label class="u-text-small-bold">{$key}</label>
									{if isset($item['help']) && $item['incorrect']}
										<a href="#"
										   class="js-popover-tooltip float-right"
										   data-js="popover"
										   data-trigger="focus"
										   data-placement="right"
										   data-content="{\App\Language::translateEncodeHtml($item['help'], $MODULE)}">
											<span class="fas fa-info-circle"></span></a>
									{/if}
								</td>
								{if $item['recommended'] === false}
									<td colspan="2" class="u-w-30per">
										<label class="u-text-small-bold">{$item['current']}</label>
									</td>
								{else}
									<td class="u-w-8per">
										<label class="u-text-small-bold">{App\Language::translate($item['recommended'], $MODULE)}</label>
									</td>
									<td class="u-w-30per">
										<label class="u-text-small-bold">{App\Language::translate($item['current'], $MODULE)}</label>
									</td>
									<td class="u-w-30per">
										<label class="u-text-small-bold">{App\Language::translate($item['cli'], $MODULE)}</label>
									</td>
								{/if}
							</tr>
						{/foreach}
						</tbody>
					</table>
					<br>
					<table class="table table-bordered table-sm u-word-break-all">
						<thead>
						<tr class="blockHeader">
							<th colspan="2" class="mediumWidthType">
								{App\Language::translate('LBL_DENY_PUBLIC_DIR_TITLE', $MODULE)}
							</th>
						</tr>
						<tr class="blockHeader">
							<th colspan="1" class="mediumWidthType">
								<span>{App\Language::translate('LBL_PUBLIC_DIR', $MODULE)}</span>
							</th>
							<th colspan="1" class="mediumWidthType">
								<span>{App\Language::translate('LBL_DENY_PUBLIC_DIR_STATUS', $MODULE)}</span>
							</th>
						</tr>
						</thead>
						<tbody>
						{foreach from=Settings_ConfReport_Module_Model::getDenyPublicDirState() key=key item=item}
							<tr {if $item.status}class="table-danger"{/if}>
								<td>
									<label class="u-text-small-bold">{$key}</label>
									{if isset($item.help) && $item.status}<a href="#"
																			 class="js-popover-tooltip float-right"
																			 data-js="popover" data-trigger="focus"
																			 data-placement="right"
																			 data-content="{\App\Language::translateEncodeHtml($item.help, $MODULE)}">
											<span class="fas fa-info-circle"></span></a>{/if}
								</td>
								<td>
									<label class="u-text-small-bold">
										{if $item.status}
											{App\Language::translate('LBL_NO', $MODULE)}
										{else}
											{App\Language::translate('LBL_YES', $MODULE)}
										{/if}
									</label>
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div id="Permissions" class="tab-pane fade">
			<table class="table table-bordered table-sm u-word-break-all">
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
					<tr {if $item.permission eq 'FailedPermission'}class="table-danger"{/if}>
						<td class="u-w-30per"><label
									class="u-text-small-bold">{App\Language::translate($key, $MODULE)}</label></td>
						<td class="u-w-30per"><label
									class="u-text-small-bold">{App\Language::translate($item.path, $MODULE)}</label>
						</td>
						<td class="u-w-30per"><label class="u-text-small-bold">
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
	</div>
</div>
