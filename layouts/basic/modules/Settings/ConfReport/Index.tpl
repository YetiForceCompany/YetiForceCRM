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
			<button id="download-image" class="btn btn-outline-dark float-right"><span class="fas fa-file-image"></span> {\App\Language::translate('LBL_IMAGE_DOWNLOAD')}</button>
		</div>
	</div>
	<div class="badge badge-info my-2">
		<a> {App\Language::translate('LBL_CONFREPORT_DESCRIPTION', $MODULE)}</a>
	</div>
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link active" data-toggle="tab" href="#Configuration">{App\Language::translate('LBL_YETIFORCE_ENGINE', $MODULE)}</a>
		</li>
		<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#Permissions">{App\Language::translate('LBL_FILES_PERMISSIONS', $MODULE)}</a></li>
	</ul>
	<div class="tab-content">
		<div id="Configuration" class="tab-pane fade in active show">
			<div class="row">
				<div class="col-lg-12 col-xl-6">
					<table class="table table-bordered table-sm my-2">
						<thead>
						<tr>
							<th colspan="4">
								{App\Language::translate('LBL_SYSTEM_STABILITY', $MODULE)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_PARAMETER', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_RECOMMENDED', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_WWW_VALUE', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_CLI_VALUE', $MODULE)}
							</th>
						</tr>
						</thead>
						<tbody class="small u-word-break-all">
						{foreach from=$STABILITY_CONF key=key item=item}
							<tr {if $item['incorrect']}class="table-danger"{/if}>
								<td class="text-nowrap bg-light">
									{$key}
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
									<td colspan="2">
										{$item['current']}
									</td>
								{else}
									<td>
										{App\Language::translate($item['recommended'], $MODULE)}
									</td>
									<td>
										{App\Language::translate($item['current'], $MODULE)}
									</td>
									<td>
										{App\Language::translate($item['cli'], $MODULE)}
									</td>
								{/if}
							</tr>
						{/foreach}
						</tbody>
					</table>
					<table class="table table-bordered table-sm my-2">
						<thead>
						<tr>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_LIBRARY', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_INSTALLED', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_MANDATORY', $MODULE)}
							</th>
						</tr>
						</thead>
						<tbody class="small u-word-break-all">
						{foreach from=Settings_ConfReport_Module_Model::getLibrary() key=key item=item}
							<tr {if $item.status == 'LBL_NO'}class="table-danger"{/if}>
								<td class="text-nowrap bg-light">
									{App\Language::translate($key,$MODULE)}
									{if isset($item.help) && $item.status}<a href="#"
																			 class="js-popover-tooltip float-right"
																			 data-js="popover" data-trigger="focus"
																			 data-placement="right"
																			 data-content="{App\Language::translate($item.help, $MODULE)}">
											<span class="fas fa-info-circle"></span></a>{/if}
								</td>
								<td>
									{App\Language::translate($item.status, $MODULE)}
								</td>
								<td>
									{if $item.mandatory}
										{App\Language::translate('LBL_MANDATORY', $MODULE)}
									{else}
										{App\Language::translate('LBL_OPTIONAL', $MODULE)}
									{/if}
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
					<table class="table table-bordered table-sm my-2">
						<thead>
						<tr>
							<th colspan="3" scope="col">
								{App\Language::translate('LBL_ENVIRONMENTAL_INFORMATION', $MODULE)}
							</th>
						</tr>
						<tr>
							<th colspan="1" class="w-25" scope="col">
								{App\Language::translate('LBL_PARAMETER', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_WWW_VALUE', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_CLI_VALUE', $MODULE)}
							</th>
						</tr>
						</thead>
						<tbody class="small u-word-break-all">
						{foreach from=$SYSTEM_INFO key=key item=item}
							<tr>
								<td class="text-nowrap bg-light">
									{App\Language::translate($key, $MODULE)}
								</td>
								{if is_array($item)}
									<td>
										{App\Language::translate($item['www'], $MODULE)}
									</td>
									<td>
										{App\Language::translate($item['cli'], $MODULE)}
									</td>
								{else}
									<td colspan="2">
										{$item}
									</td>
								{/if}
							</tr>
						{/foreach}
						</tbody>
					</table>
				</div>
				<div class="col-lg-12 col-xl-6">
					<table class="table table-bordered table-sm my-2">
						<thead>
						<tr>
							<th colspan="3" scope="col">
								{App\Language::translate('LBL_SYSTEM_SECURITY', $MODULE)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_PARAMETER', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_RECOMMENDED', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_VALUE', $MODULE)}
							</th>
						</tr>
						</thead>
						<tbody class="small u-word-break-all">
						{foreach from=$SECURITY_CONF key=key item=item}
							<tr {if $item.status}class="table-danger"{/if}>
								<td class="text-nowrap bg-light">
									{$key}
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
									{App\Language::translate($item.recommended, $MODULE)}
								</td>
								<td>
									{App\Language::translate($item.current, $MODULE)}
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
					<table class="table table-bordered table-sm my-2">
						<thead>
						<tr>
							<th colspan="2" scope="col">
								{App\Language::translate('LBL_DENY_PUBLIC_DIR_TITLE', $MODULE)}
							</th>
						</tr>
						<tr>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_PUBLIC_DIR', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								{App\Language::translate('LBL_DENY_PUBLIC_DIR_STATUS', $MODULE)}
							</th>
						</tr>
						</thead>
						<tbody class="small u-word-break-all">
						{foreach from=Settings_ConfReport_Module_Model::getDenyPublicDirState() key=key item=item}
							<tr {if $item.status}class="table-danger"{/if}>
								<td class="text-nowrap bg-light">
									{$key}
									{if isset($item.help) && $item.status}<a href="#"
																			 class="js-popover-tooltip float-right"
																			 data-js="popover" data-trigger="focus"
																			 data-placement="right"
																			 data-content="{\App\Language::translateEncodeHtml($item.help, $MODULE)}">
											<span class="fas fa-info-circle"></span></a>{/if}
								</td>
								<td>
									{if $item.status}
										{App\Language::translate('LBL_NO', $MODULE)}
									{else}
										{App\Language::translate('LBL_YES', $MODULE)}
									{/if}
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
					<table class="table table-bordered table-sm my-2">
						<thead>
						<tr>
							<th colspan="3" scope="col">
								{App\Language::translate('LBL_DATABASE_INFORMATION', $MODULE)}
							</th>
						</tr>
						<tr>
							<th colspan="1" class="w-25" scope="col">
								{App\Language::translate('LBL_PARAMETER', $MODULE)}
							</th>
							<th colspan="1" class="w-25" scope="col">
								{App\Language::translate('LBL_RECOMMENDED', $MODULE)}
							</th>
							<th colspan="1" scope="col">
								<span>{App\Language::translate('LBL_VALUE', $MODULE)}</span>
							</th>
						</tr>
						</thead>
						<tbody class="small u-word-break-all">
						{foreach from=$DB_CONF key=key item=item}
							<tr {if $item['status']}class="table-danger"{/if}>
								<td class="text-nowrap bg-light">
									{App\Language::translate($key, $MODULE)}
									{if isset($item.help) && $item.status}<a href="#"
																			 class="js-popover-tooltip float-right"
																			 data-js="popover" data-trigger="focus"
																			 data-placement="right"
																			 data-content="{\App\Language::translateEncodeHtml($item.help, $MODULE)}">
											<span class="fas fa-info-circle"></span></a>{/if}
								</td>
								{if $item['recommended'] === false}
									<td colspan="2">{$item['current']}</td>
								{else}
									<td>{$item['recommended']}</td>
									<td>{$item['current']}</td>
								{/if}
							</tr>
						{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div id="Permissions" class="tab-pane fade">
			<table class="table table-bordered table-sm my-2">
				<thead>
				<tr>
					<th colspan="1" scope="col">
						<span>{App\Language::translate('LBL_FILE', $MODULE)}</span>
					</th>
					<th colspan="1" scope="col">
						<span>{App\Language::translate('LBL_PATH', $MODULE)}</span>
					</th>
					<th colspan="1" scope="col">
						<span>{App\Language::translate('LBL_PERMISSION', $MODULE)}</span>
					</th>
				</tr>
				</thead>
				<tbody class="small">
				{foreach from=Settings_ConfReport_Module_Model::getPermissionsFiles() key=key item=item}
					<tr {if $item.permission eq 'FailedPermission'}class="table-danger"{/if}>
						<td class="text-nowrap bg-light">{App\Language::translate($key, $MODULE)}</td>
						<td>{App\Language::translate($item.path, $MODULE)}
						</td>
						<td>
							{if $item.permission eq 'FailedPermission'}
								{App\Language::translate('LBL_FAILED_PERMISSION', $MODULE)}
							{else}
								{App\Language::translate('LBL_TRUE_PERMISSION', $MODULE)}
							{/if}
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>
