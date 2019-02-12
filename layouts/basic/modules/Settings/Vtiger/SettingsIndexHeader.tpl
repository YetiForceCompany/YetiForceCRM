{strip}
	<div class="widget_header row ">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="row m-0 mt-2">
		<ul class="nav nav-tabs massEditTabs js-tabs" data-js="click">
			<li class="nav-item" data-mode="index">
				<a class="active nav-link" href="#" data-toggle="tab">
					<span class="fas fa-home fa-fw mr-1"></span>
					{\App\Language::translate('LBL_START', $QUALIFIED_MODULE)}
				</a>
			</li>
			<li class="nav-item" data-mode="github">
				<a class="nav-link" href="#" data-toggle="tab">
					<span class="fab fa-github fa-fw mr-1"></span>
					{\App\Language::translate('LBL_GITHUB', $QUALIFIED_MODULE)}
				</a>
			</li>
			<li class="nav-item" data-mode="systemWarnings">
				<a class="nav-link" href="#" data-toggle="tab">
					<span class="fas fa-exclamation-triangle fa-fw mr-1"></span>
					{\App\Language::translate('LBL_SYSTEM_WARNINGS', $QUALIFIED_MODULE)}
				</a>
			</li>
			<li class="nav-item" data-mode="security">
				<a class="nav-link" href="#" data-toggle="tab">
					<span class="adminIcon-security position-relative u-position-t-2px u-font-size-19px mr-1"
						  aria-hidden="true"></span>
					{\App\Language::translate('LBL_SECURITY', $QUALIFIED_MODULE)}
				</a>
			</li>
		</ul>
	</div>
	<div class="indexContainer"></div>
{/strip}
