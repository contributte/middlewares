<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tracy\Debugger;

class TracyMiddleware implements IMiddleware
{

	protected bool $enable = false;

	protected bool|string $mode = Debugger::Development;

	protected ?string $logDir = null;

	protected ?string $email = null;

	public function __construct(bool|string $mode = Debugger::Development, ?string $logDir = null, ?string $email = null)
	{
		$this->mode = $mode;
		$this->logDir = $logDir;
		$this->email = $email;
	}

	public static function factory(bool $debugMode = false): TracyMiddleware
	{
		$tm = new TracyMiddleware();
		if ($debugMode) {
			$tm->enable();
		}

		return $tm;
	}

	public function enable(): void
	{
		$this->enable = true;
	}

	public function disable(): void
	{
		$this->enable = false;
	}

	public function setMode(bool|string $mode): void
	{
		$this->mode = $mode;
	}

	public function setLogDir(?string $logDir): void
	{
		$this->logDir = $logDir;
	}

	public function setEmail(?string $email): void
	{
		$this->email = $email;
	}

	/**
	 * Catch all exceptions
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next): ResponseInterface
	{
		if ($this->enable === true) {
			Debugger::enable($this->mode, $this->logDir, $this->email);
		}

		return $next($psr7Request, $psr7Response);
	}

}
