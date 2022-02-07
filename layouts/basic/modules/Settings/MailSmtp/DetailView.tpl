{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="o-breadcrumb widget_header row">
		<div class="col-md-8">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
		<div class="col-md-4 mt-3">
			<div class="float-right btn-toolbar">
				<a class="btn btn-info" role="button" href="{$RECORD_MODEL->getEditViewUrl()}">
					<span class="yfi yfi-full-editing-view mr-2" title="{App\Language::translate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}"></span>
					<span class="sr-only">{App\Language::translate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}</span>
					<strong>{App\Language::translate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}</strong>
				</a>
				<button type="button" class="btn btn-danger ml-2 js-remove" data-js="click" data-record-id="{$RECORD_MODEL->getId()}">
					<span class="fas fa-trash-alt mr-2"></span>
					<strong>{App\Language::translate('LBL_DELETE_RECORD', $QUALIFIED_MODULE)}</strong>
				</button>
			</div>
		</div>
	</div>
	<div class="detailViewInfo">
		<table class="table table-bordered">
			<thead class="thead-light">
				<tr>
					<th colspan="2" class="{$WIDTHTYPE}"><strong>{App\Language::translate('LBL_SMTP_DETAIL',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('name')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_MAILER_TYPE', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('mailer_type')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_DEFAULT', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('default')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_HOST', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('host')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_PORT', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('port')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_AUTHENTICATION', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('authentication')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_USERNAME', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('username')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_PASSWORD', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('password')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_INDIVIDUAL_DELIVERY', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('individual_delivery')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_SECURE', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('secure')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_FROM_NAME', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('from_name')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_FROM_EMAIL', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('from_email')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_REPLY_TO', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('reply_to')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_OPTIONS', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('options')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_MAIL_PRIORITY', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('priority')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_CONFIRM_READING_TO', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('confirm_reading_to')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_ORGANIZATION', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('organization')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_UNSUBSCIBE', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('unsubscribe')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_SAVE_SEND_MAIL', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('save_send_mail')}
					</td>
				</tr>
			</tbody>
		</table>
		{if $RECORD_MODEL->get('save_send_mail') eq 1}
			<table class="table table-bordered">
				<thead>
					<tr class="blockHeader">
						<th colspan="2" class="{$WIDTHTYPE} col-md-12"><strong>{App\Language::translate('LBL_IMAP_SAVE_MAIL',$QUALIFIED_MODULE)}</strong></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_HOST', $QUALIFIED_MODULE)}</label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('smtp_host')}
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_PORT', $QUALIFIED_MODULE)}</label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('smtp_port')}
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_USERNAME', $QUALIFIED_MODULE)}</label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('smtp_username')}
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_PASSWORD', $QUALIFIED_MODULE)}</label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('smtp_password')}
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_SEND_FOLDER', $QUALIFIED_MODULE)}</label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('smtp_folder')}
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE} w-25"><label class="float-right">{App\Language::translate('LBL_VALIDATE_CERT', $QUALIFIED_MODULE)}</label></td>
						<td class="{$WIDTHTYPE} w-75">
							{$RECORD_MODEL->getDisplayValue('smtp_validate_cert')}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
	</div>
	{strip}
