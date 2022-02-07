{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class=" usersAuth">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="mt-2">
			<div class="contents tabbable">
				<ul class="nav nav-tabs layoutTabs massEditTabs">
					<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#ldap"><strong>{\App\Language::translate('LBL_LDAP_AUTH', $QUALIFIED_MODULE)}</strong></a></li>
				</ul>
				<div class="tab-content layoutContent py-3">
					{assign var=CONFIG value=$MODULE_MODEL->getConfig('ldap')}
					<div class="tab-pane active" id="ldap">
						<div class="alert alert-info alert-dismissible" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
							</button>
							<p>
								<span class="fas fa-exclamation-circle"></span>&nbsp;&nbsp;
								{\App\Language::translate('LBL_LDAP_RECOMMENDED_INFO', $QUALIFIED_MODULE)}
							</p>
						</div>
						<div class="clearfix"></div>
						<div class="form-row">
							<div class="col-md-1 col-sm-1 col-2 pagination-centered">
								<input class="configField" type="checkbox" name="active" id="ldapActive" data-type="ldap" value="1" {if $CONFIG['active']=='true'}checked="" {/if}>
							</div>
							<div class="col-md-11 col-sm-11 col-10">
								<label class="u-text-small-bold" for="ldapActive">{\App\Language::translate('LBL_ACTIVE_LDAP_AUTH', $QUALIFIED_MODULE)}</label>
							</div>
						</div>
						<hr />
						<div class="form-row pt-2">
							<div class="col-md-12 col-lg-3">
								<label class="u-text-small-bold" for="showMailIcon">{\App\Language::translate('LBL_LDAP_SERVER', $QUALIFIED_MODULE)}</label>
							</div>
							<div class="col-md-12 col-lg-8">
								<input class="configField form-control" title="{\App\Language::translate('LBL_LDAP_SERVER', $QUALIFIED_MODULE)}" type="text" name="server" data-type="ldap" value="{$CONFIG['server']}">
							</div>
						</div>
						<div class="form-row pt-3">
							<div class="col-md-12 col-lg-3">
								<label class="u-text-small-bold" for="showMailIcon">{\App\Language::translate('LBL_LDAP_DOMAIN', $QUALIFIED_MODULE)}</label>
							</div>
							<div class="col-md-12 col-lg-8">
								<div class="input-group">
									<input class="configField form-control" title="{\App\Language::translate('LBL_LDAP_DOMAIN', $QUALIFIED_MODULE)}" type="text" name="domain" data-type="ldap" value="{$CONFIG['domain']}">
									<span class="input-group-append js-popover-tooltip" data-js="popover" data-content="@testlab.local (DC=testlab,DC=local)">
										<div class="input-group-text"><span class="fas fa-info-circle"></span></div>
									</span>
								</div>
							</div>
						</div>
						<div class="form-row pt-3">
							<div class="col-md-12 col-lg-3">
								<label class="u-text-small-bold" for="showMailIcon">{\App\Language::translate('LBL_LDAP_PORT', $QUALIFIED_MODULE)}</label>
							</div>
							<div class="col-md-12 col-lg-8">
								<input class="configField form-control" title="{\App\Language::translate('LBL_LDAP_PORT', $QUALIFIED_MODULE)}" type="text" name="port" data-type="ldap" value="{$CONFIG['port']}">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
