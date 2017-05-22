<?php

/**
 * Progress class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Progress
{

	public $name = 'Progress';
	public $sequence = 5;
	public $reference = 'Details';

	public function process($instance)
	{
		return $instance->get('progress');
	}
}
