{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-ConfigEditor-Detail -->
	<div id="ConfigEditorDetails">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div>
			<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'Main'}active{/if}" href="#Main" data-toggle="tab" data-name="Main">
						<span class="fas fa-sliders-h mr-2"></span>{\App\Language::translate('LBL_MAIN_CONFIG', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'Relation'}active{/if}" href="#Relation" data-toggle="tab" data-name="Relation">
						<span class="adminIcon-modules-relations mr-2"></span>{\App\Language::translate('LBL_RELATION_CONFIG', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'Performance'}active{/if}" href="#Performance" data-toggle="tab" data-name="Performance">
						<span class="fa-solid fa-gauge-high mr-2"></span>{\App\Language::translate('LBL_PERFORMANCE', $QUALIFIED_MODULE)}
					</a>
				</li>
			</ul>
		</div>
		<div id="my-tab-content" class="tab-content">
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'Main'}active{/if}" id="Main" data-name="Main" data-js="data">
				{include file=\App\Layout::getTemplatePath('MainDetail.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'Relation'}active{/if}" id="Relation" data-name="Relation" data-js="data">
				{include file=\App\Layout::getTemplatePath('Relation.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'Performance'}active{/if}" id="Performance" data-name="Performance" data-js="data">
				{include file=\App\Layout::getTemplatePath('Performance.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-ConfigEditor-Detail -->
{/strip}
