{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="alert alert-danger" role="alert">
		<div>
			<h4>{vtranslate('LBL_MISSING_LIBRARY_TITLE', $QUALIFIED_MODULE)}</h4>
		</div>
		<div>
			{vtranslate('LBL_MISSING_LIBRARY_DESC', $QUALIFIED_MODULE)}
		</div>
		<div>
			<ul>
				{foreach item=LIB key=NAME from=$MISSING_LIBRARY}
					<li>
						<strong>{$LIB['dir']}</strong> (<a href="{$LIB['url']}" target="_blank">{$LIB['url']}</a>) <a href="index.php?module=ModuleManager&parent=Settings&action=Basic&mode=downloadLibrary&name={$NAME}" class="btn btn-primary btn-xs">{vtranslate('BTN_DOWNLOAD', $QUALIFIED_MODULE)}</a>
					</li>
				{/foreach}
			</ul>
		</div>
		<div>
			{vtranslate('LBL_NO_INTERNET_CONNECTION', $QUALIFIED_MODULE,Settings_ModuleManager_Library_Model::$tempDir)}
		</div>
		<div>
			<br/><a href="index.php?module=ModuleManager&parent=Settings&action=Basic&mode=downloadLibrary" class="btn btn-primary">{vtranslate('BTN_DOWNLOAD_ALL', $QUALIFIED_MODULE)}</a>
		</div>
	</div>
{/strip}
