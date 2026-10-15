<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Psr\Log\AbstractLogger;
use Stringable;

final class MemoryLogger extends AbstractLogger
{

	/** @var array<array{level: mixed, message: string, context: mixed[]}> */
	public array $logs = [];

	/**
	 * @param mixed[] $context
	 */
	public function log(mixed $level, string|Stringable $message, array $context = []): void
	{
		$this->logs[] = [
			'level' => $level,
			'message' => (string) $message,
			'context' => $context,
		];
	}

}
