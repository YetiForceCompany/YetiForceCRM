{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="o-breadcrumb widget_header row">
		<div class="col-md-8">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
		<div class="col-md-4 d-flex align-items-center justify-content-end">
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
					<th colspan="2" class="{$WIDTHTYPE}"><strong>{App\Language::translate('LBL_MAIL_CLIENT_DETAIL', $QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_VALIDATE_CERT', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('validate_cert')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_ADD_TYPE', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('add_connection_type')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_IMAP_SERVER', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('default_host')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_PORT_CONNECT_IMAP', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('default_port')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_SMTP_SERVER', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('smtp_server')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_SMTP_PORT', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('smtp_port')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_LANGUAGE', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('language')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_DOMAIN_AUTOMATICALLY', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('username_domain')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_IP_ADDRESS', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('ip_check')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_ENABLE_SPELL_CHECK', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('enable_spellcheck')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_ACCESS_IDENTITY', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('identities_level')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE} w-25" ><label class="float-right">{App\Language::translate('LBL_LIFE_SESSION', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE} w-75">
						{$RECORD_MODEL->getDisplayValue('session_lifetime')}
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	{strip}
