{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Proxy-Edit">
		<div class="contents">
			<form id="ConfigProxyForm" class="form-horizontal" method="POST">
				<div class="row widget_header">
					<div class="col-md-8">
						{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
					</div>
				</div>
				<hr>
				<div class="alert alert-block alert-info mb-2">
					<button	button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<span>
						{\App\Language::translate('LBL_PROXY_INFORMATION', $QUALIFIED_MODULE)}
					</span>
				</div>
				<table class="table table-bordered table-sm themeTableColor">
					<thead>
						<tr class="blockHeader">
							<th colspan="2"
								class="{$WIDTHTYPE}">{\App\Language::translate('LBL_PROXY_CONFIGRATION', $QUALIFIED_MODULE)}</th>
						</tr>
					</thead>
					<tbody>
						{foreach key=FIELD_NAME item=FIELD_LABEL from=$MODULE_MODEL->listFields}
							{assign var="FIELD_MODEL" value=$MODULE_MODEL->getFieldInstanceByName($FIELD_NAME)->set('fieldvalue', $MODULE_MODEL->get($FIELD_NAME))}
							<tr>
								<td width="30%" class="{$WIDTHTYPE} text-left">
									<div class="form-row">
										<label class="col-form-label col-md-4 u-text-small-bold text-left text-md-right">
											{\App\Language::translate($FIELD_LABEL, $QUALIFIED_MODULE)}
										</label>
										<div class="col-md-3 fieldValue">
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=null }
										</div>
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</form>
		</div>
	</div>
{/strip}
