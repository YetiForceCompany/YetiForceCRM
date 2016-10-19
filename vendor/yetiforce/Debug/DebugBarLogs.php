<?php
namespace App\Debug;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataFormatter\DataFormatterInterface;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\MessagesAggregateInterface;

/**
 * Provides a way to log messages
 */
class DebugBarLogs implements DataCollectorInterface, MessagesAggregateInterface, Renderable
{

	protected $name;
	protected $messages = array();
	protected $aggregates = array();
	protected $dataFormater;

	/**
	 * @param string $name
	 */
	public function __construct($name = 'logs')
	{
		$this->name = $name;
	}

	/**
	 * Sets the data formater instance used by this collector
	 *
	 * @param DataFormatterInterface $formater
	 * @return $this
	 */
	public function setDataFormatter(DataFormatterInterface $formater)
	{
		$this->dataFormater = $formater;
		return $this;
	}

	/**
	 * @return DataFormatterInterface
	 */
	public function getDataFormatter()
	{
		if ($this->dataFormater === null) {
			$this->dataFormater = DataCollector::getDefaultDataFormatter();
		}
		return $this->dataFormater;
	}

	/**
	 * Adds a message
	 *
	 * A message can be anything from an object to a string
	 *
	 * @param mixed $message
	 * @param string $label
	 */
	public function addMessage($message, $label = 'info', $traces = [])
	{
		if (!is_string($traces)) {
			$traces = $this->getDataFormatter()->formatVar($traces);
		}
		$this->messages[] = array(
			'message' => $message,
			'label' => $label,
			'trace' => $traces
		);
	}

	/**
	 * Aggregates messages from other collectors
	 *
	 * @param MessagesAggregateInterface $messages
	 */
	public function aggregate(MessagesAggregateInterface $messages)
	{
		$this->aggregates[] = $messages;
	}

	/**
	 * @return array
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * Deletes all messages
	 */
	public function clear()
	{
		$this->messages = array();
	}

	/**
	 * @return array
	 */
	public function collect()
	{
		$messages = $this->getMessages();
		return array(
			'count' => count($messages),
			'messages' => $messages
		);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getWidgets()
	{
		$name = $this->getName();
		return array(
			"$name" => array(
				'icon' => 'list-alt',
				"widget" => "PhpDebugBar.Widgets.DebugLogsWidget",
				"map" => "$name.messages",
				"default" => "[]"
			),
			"$name:badge" => array(
				"map" => "$name.count",
				"default" => "null"
			)
		);
	}
}
