<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Application;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\IMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

abstract class AbstractApplication implements IApplication
{

	public const LISTENER_STARTUP = 'startup';
	public const LISTENER_REQUEST = 'request';
	public const LISTENER_ERROR = 'error';
	public const LISTENER_RESPONSE = 'response';

	/** @var callable|IMiddleware */
	private $chain;

	private bool $catchExceptions = false;

	/** @var callable[][] */
	private array $listeners = [
		self::LISTENER_STARTUP => [],
		self::LISTENER_REQUEST => [],
		self::LISTENER_ERROR => [],
		self::LISTENER_RESPONSE => [],
	];

	public function __construct(callable|IMiddleware $chain)
	{
		$this->chain = $chain;
	}

	public function setCatchExceptions(bool $catch = true): void
	{
		$this->catchExceptions = $catch;
	}

	/**
	 * Dispatch application in middleware cycle!
	 */
	public function run(): ResponseInterface
	{
		// Create initial request (PSR7!)
		$request = $this->createInitialRequest();

		return $this->runWith($request);
	}

	/**
	 * Dispatch application in middleware cycle!
	 */
	public function runWith(ServerRequestInterface $request): ResponseInterface
	{
		// Trigger event!
		$this->dispatch($this->listeners[self::LISTENER_STARTUP], [$this]);

		// Create initial response (PSR7!)
		$response = $this->createInitialResponse();

		try {
			// Trigger event!
			$this->dispatch($this->listeners[self::LISTENER_REQUEST], [$this, $request, $response]);

			// Right to the cycle
			$response = call_user_func(
				$this->chain,
				$request,
				$response,
				fn (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface => $response
			);

			// Response validation check
			if (!isset($response)) {
				throw new RuntimeException('Final response cannot be NULL or unset');
			}
		} catch (Throwable $e) {
			// Trigger event! In case of manual handling error, returned object is passed.
			$res = $this->dispatch($this->listeners[self::LISTENER_ERROR], [$this, $e, $request, $response]);
			if ($res instanceof ResponseInterface) {
				return $res;
			}

			// Throw exception again if it's not caught
			if ($this->catchExceptions !== true) {
				throw $e;
			}
		}

		// Trigger event! In case of manual finalizing, returned object is passed.
		$res = $this->dispatch($this->listeners[self::LISTENER_RESPONSE], [$this, $request, $response]);
		if ($res instanceof ResponseInterface) {
			return $res;
		}

		// Send to finalizer (simple send response)
		return $this->finalize($request, $response);
	}

	public function addListener(string $type, callable $listener): void
	{
		if (!in_array($type, [self::LISTENER_STARTUP, self::LISTENER_REQUEST, self::LISTENER_ERROR, self::LISTENER_RESPONSE], true)) {
			throw new InvalidStateException(sprintf('Given type "%s" is not supported', $type));
		}

		$this->listeners[$type][] = $listener;
	}

	abstract protected function createInitialRequest(): ServerRequestInterface;

	abstract protected function createInitialResponse(): ResponseInterface;

	abstract protected function finalize(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

	/**
	 * @param callable[] $handlers
	 * @param mixed[] $arguments
	 */
	protected function dispatch(array $handlers, array $arguments): mixed
	{
		// Default return value
		$ret = null;

		// Iterate over all events
		foreach ($handlers as $handler) {
			// Take all arguments with last return value
			// and pass to callback handler
			$ret = call_user_func_array($handler, array_merge($arguments, (array) $ret));
		}

		return $ret;
	}

}
