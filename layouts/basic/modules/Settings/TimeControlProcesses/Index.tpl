{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="processesContainer">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<ul id="tabs" class="nav nav-tabs mt-1" data-tabs="tabs">
			<li class="nav-item"><a class="nav-link active" href="#general" data-toggle="tab">{\App\Language::translate('LBL_GENERAL_SETTINGS', $QUALIFIED_MODULE)}</a></li>
			<li class="nav-item"><a href="#timeControlWidget" class="nav-link" data-toggle="tab">{\App\Language::translate('LBL_TIME_CONTROL_WIDGET', $QUALIFIED_MODULE)}</a></li>
		</ul>
		<br />
		<div class="tab-content">
			<div class='editViewContainer tab-pane active' id="general" data-type="general">
				{assign var=GENERAL_FIELDS value=$MODULE_MODEL->get('general')}
				<div class="btn-group-toggle" data-toggle="buttons">
					<label class="btn {if $GENERAL_FIELDS.oneDay eq 'true'}btn-success active{else}btn-light{/if} btn-block">
						<input autocomplete="off" type="checkbox" name="oneDay" {if $GENERAL_FIELDS.oneDay eq 'true'}checked{/if}>{\App\Language::translate('LBL_ONEDAY_VALID', $QUALIFIED_MODULE)}
						<span class="far {if $GENERAL_FIELDS.oneDay eq 'true'}fa-check-square{else}fa-square{/if} float-left"></span>
					</label>
					<label class="btn {if $GENERAL_FIELDS.timeOverlap eq 'true'}btn-success active{else}btn-light{/if} btn-block">
						<input autocomplete="off" type="checkbox" name="timeOverlap" {if $GENERAL_FIELDS.timeOverlap eq 'true'}checked{/if}>{\App\Language::translate('LBL_TIMEOVERLAP_VALID', $QUALIFIED_MODULE)}
						<span class="far {if $GENERAL_FIELDS.timeOverlap eq 'true'}fa-check-square{else}fa-square{/if} float-left"></span>
					</label>
				</div>
			</div>
			<div class="tab-pane editViewContainer" id="timeControlWidget" data-type="timeControlWidget">
				<div class="alert alert-info" role="alert">{\App\Language::translate('LBL_TCW_INFO', $QUALIFIED_MODULE)}</div>
				{assign var=TCW_FIELDS value=$MODULE_MODEL->get('timeControlWidget')}
				<div class="btn-group-toggle" data-toggle="buttons">
					<label class="btn {if $TCW_FIELDS.holidays eq 'true'}btn-success active{else}btn-light{/if} btn-block">
						<input autocomplete="off" type="checkbox" name="holidays" {if $TCW_FIELDS.holidays eq 'true'}checked{/if}> {\App\Language::translate('LBL_HOLIDAYS', $QUALIFIED_MODULE)}
						<span class="far {if $TCW_FIELDS.holidays eq 'true'}fa-check-square{else}fa-square{/if} float-left"></span>
					</label>
					<label class="btn {if $TCW_FIELDS.workingDays eq 'true'}btn-success active{else}btn-light{/if} btn-block">
						<input autocomplete="off" type="checkbox" name="workingDays" {if $TCW_FIELDS.workingDays eq 'true'}checked{/if}> {\App\Language::translate('LBL_WORKING_DAYS', $QUALIFIED_MODULE)}
						<span class="far {if $TCW_FIELDS.workingDays eq 'true'}fa-check-square{else}fa-square{/if} float-left"></span>
					</label>
					<label class="btn {if $TCW_FIELDS.workingTime eq 'true'}btn-success active{else}btn-light{/if} btn-block">
						<input autocomplete="off" type="checkbox" name="workingTime" {if $TCW_FIELDS.workingTime eq 'true'}checked{/if}> {\App\Language::translate('LBL_WORKING_TIME', $QUALIFIED_MODULE)}
						<span class="far {if $TCW_FIELDS.workingTime eq 'true'}fa-check-square{else}fa-square{/if} float-left"></span>
					</label>
				</div>

			</div>
		</div>
	</div>
{strip}
