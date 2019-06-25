<?php

namespace App\Debug;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\MessagesAggregateInterface;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataFormatter\DataFormatterInterface;

/**
 * Provides a way to log messages.
 */
class DebugBarLogs implements DataCollectorInterface, MessagesAggregateInterface, Renderable
{
	protected $name;
	protected $messages = [];
	protected $aggregates = [];
	protected $dataFormater;

	/**
	 * @param string $name
	 */
	public function __construct($name = 'logs')
	{
		$this->name = $name;
	}

	/**
	 * Sets the data formater instance used by this collector.
	 *
	 * @param DataFormatterInterface $formater
	 *
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
		if (null === $this->dataFormater) {
			$this->dataFormater = DataCollector::getDefaultDataFormatter();
		}
		return $this->dataFormater;
	}

	/**
	 * Adds a message.
	 *
	 * A message can be anything from an object to a string
	 *
	 * @param mixed  $message
	 * @param string $label
	 * @param mixed  $traces
	 */
	public function addMessage($message, $label = 'info', $traces = [])
	{
		if (!\is_string($traces)) {
			$traces = $this->getDataFormatter()->formatVar($traces);
		}
		$this->messages[] = [
			'message' => $message,
			'label' => $label,
			'trace' => $traces,
		];
	}

	/**
	 * Aggregates messages from other collectors.
	 *
	 * @param MessagesAggregateInterface $messagesAggregate
	 */
	public function aggregate(MessagesAggregateInterface $messagesAggregate)
	{
		$this->aggregates[] = $messagesAggregate;
	}

	/**
	 * @return array
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * Deletes all messages.
	 */
	public function clear()
	{
		$this->messages = [];
	}

	/**
	 * @return array
	 */
	public function collect()
	{
		$messagesCollect = $this->getMessages();

		return [
			'count' => \count($messagesCollect),
			'messages' => $messagesCollect,
		];
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
		$widgetName = $this->getName();

		return [
			"$widgetName" => [
				'icon' => 'list-alt',
				'widget' => 'PhpDebugBar.Widgets.DebugLogsWidget',
				'map' => "$widgetName.messages",
				'default' => '[]',
			],
			"$widgetName:badge" => [
				'map' => "$widgetName.count",
				'default' => 'null',
			],
		];
	}
}
