<?php
/**
 * Manages data collectors. DebugBar provides an array-like access  to collectors by name.
 *
 * @package Log
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Debug\DebugBar;

/**
 * Manages data collectors. DebugBar provides an array-like access  to collectors by name.
 */
class DebugBar extends \DebugBar\DebugBar
{
	/**
	 * Returns a JavascriptRenderer for this instance.
	 *
	 * @param string $baseUrl
	 * @param string $basePath
	 *
	 * @return JavascriptRenderer
	 */
	public function getJavascriptRenderer($baseUrl = null, $basePath = null)
	{
		if (null === $this->jsRenderer) {
			$this->jsRenderer = new JavascriptRenderer($this, $baseUrl, $basePath);
			$this->jsRenderer->setOptions([
				'enable_jquery_noconflict' => false,
			]);
			$this->jsRenderer->disableVendor('jquery');
		}
		return $this->jsRenderer;
	}

	/**
	 * Renders the html to include needed assets.
	 *
	 * @return void
	 */
	public function loadScripts()
	{
		return $this->getJavascriptRenderer(\App\Layout::getPublicUrl('vendor/maximebf/debugbar/src/DebugBar/Resources'))->renderHead();
	}
}
