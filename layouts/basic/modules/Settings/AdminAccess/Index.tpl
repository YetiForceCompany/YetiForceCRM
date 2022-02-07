{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-AdminAccess-Index -->
	<div>
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div>
			<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
				<li class="nav-item">
					<a class="nav-link {if $TAB === 'permissions'}active{/if}" href="#permissions" data-toggle="tab" data-name="permissions">
						<span class="yfi-advanced-permission mr-2"></span>{\App\Language::translate('LBL_PERMISSIONS', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $TAB === 'visitPurpose'}active{/if}" href="#historyAdminsVisitPurpose" data-toggle="tab" data-name="historyAdminsVisitPurpose">
						<span class="fas fa-history mr-2"></span>{\App\Language::translate('LBL_HISTORY_ADMINS_VISIT_PURPOSE', $QUALIFIED_MODULE)}
					</a>
				</li>
			</ul>
		</div>
		<div id="my-tab-content" class="tab-content ml-1 mr-1">
			<div class="js-tab tab-pane {if $TAB === 'permissions'}active{/if} font-weight-normal" id="permissions" data-js="data"></div>
			<div class="js-tab tab-pane {if $TAB === 'visitPurpose'}active{/if} font-weight-normal" id="historyAdminsVisitPurpose" data-js="data"></div>
		</div>
	</div>
	<!-- /tpl-Settings-AdminAccess-Index -->
{/strip}
