{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="progressIndicator" class="main-container d-none">
		<div class="inner-container">
			<div class="inner-container">
				<div>
					<div class="welcome-div alignCenter">
						<h3>{\App\Language::translate('LBL_MIGRATION_IN_PROGRESS','Install')}...</h3><br>
						<img src="../{\App\Layout::getPublicUrl('layouts/basic/images/install_loading.gif')}" alt="Install loading">
						<h6>{\App\Language::translate('LBL_PLEASE_WAIT','Install')}.... </h6>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="mainContainer" class="main-container">
		<div class="inner-container">
			<h2>{\App\Language::translate('LBL_MIGRATION_HEADER', 'Install')}</h2>
			<form class="" name="step2" method="post" action="Install.php">
				<input type="hidden" name="mode" value="mStep3">
				<input type="hidden" name="lang" value="{$LANG}">
				<div>
					<div class="inner-container">
						<p>{\App\Language::translate('LBL_MIGRATION_DESC', 'Install')}</p>
						<div>
							<input type="checkbox" id="checkBox1" name="checkBox1" required><div class="chkbox"></div> {\App\Language::translate('LBL_HAVE_BACKUP_DATABASE', 'Install')}</a>
						</div>
						<br>
						<div>
							<input type="checkbox" id="checkBox2" name="checkBox2" required><div class="chkbox"></div> {\App\Language::translate('LBL_HAVE_BACKUP_FILES', 'Install')}</a>
						</div>
						<br>
						<div>
							<div class="col-md-2 paddingLRZero">{\App\Language::translate('LBL_SYSTEM_FOR_MIGRATION', 'Install')}</div>
							<div class="col-md-9 paddingLRZero">
								<div class="col-md-5 paddingLRZero">
									<select name="system" class="form-control" id="old_version" required>
										<option value="" selected="">{\App\Language::translate('LBL_SELECT', 'Install')}</option>
										{foreach key=KEY item=ITEM from=$SCHEMALISTS}
											<option value="{$KEY}">{$ITEM}</option>
										{/foreach}
									</select>
								</div>
							</div>
						</div>
						<br>
						<div>
							<div class="col-md-2 paddingLRZero">{\App\Language::translate('LBL_SOURCE_DIRECTORY', 'Install')}</div>
							<div class="col-md-9 paddingLRZero">
								<div class="col-md-5 paddingLRZero">
									<input type="text" value="" name="source_directory" id="source_directory" class="form-control" required placeholder="{\App\Language::translate('LBL_EXAMPLE_DIRECTORY', 'Install')}: {$EXAMPLE_DIRECTORY}">
								</div>
							</div>
						</div>
						<br>
						<div>
							<div class="col-md-2 paddingLRZero ">{\App\Language::translate('LBL_ADMIN_LOGIN', 'Install')}</div>
							<div class="col-md-9 paddingLRZero">
								<div class="col-md-5 paddingLRZero">
									<input type="text" value="" name="username" id="username" class="form-control" required>
								</div>
							</div>
						</div>
						<br>
						<div>
							<div class="col-md-2 paddingLRZero">{\App\Language::translate('LBL_ADMIN_PASSWORD', 'Install')}</div>
							<div class="col-md-9 paddingLRZero">
								<div class="col-md-5 paddingLRZero">
									<input type="password" value="" name="password" id="password" class="form-control" required>
								</div>
							</div>
						</div>
						<br>
					</div>
				</div>
				<div>
					<div class="button-container">
						<input id="agree" type="submit" class="btn btn-md btn-primary" value="{\App\Language::translate('LBL_START_MIGRATION', 'Install')}">
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
