<?php

namespace Contributte\Middlewares\Application;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\IMiddleware;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
abstract class AbstractApplication implements IApplication
{

	const LISTENER_STARTUP = 'startup';
	const LISTENER_REQUEST = 'request';
	const LISTENER_ERROR = 'error';
	const LISTENER_RESPONSE = 'response';

	/** @var callable|IMiddleware */
	private $chain;

	/** @var bool */
	private $catchExceptions = FALSE;

	/** @var [callable[]] */
	private $listeners = [
		self::LISTENER_STARTUP => [],
		self::LISTENER_REQUEST => [],
		self::LISTENER_ERROR => [],
		self::LISTENER_RESPONSE => [],
	];

	/**
	 * @param callable|IMiddleware $chain
	 */
	public function __construct($chain)
	{
		$this->chain = $chain;
	}

	/**
	 * @param bool $catch
	 * @return void
	 */
	public function setCatchExceptions($catch = TRUE)
	{
		$this->catchExceptions = $catch;
	}

	/**
	 * Dispatch application in middleware cycle!
	 *
	 * @return ResponseInterface
	 */
	public function run()
	{
		// Trigger event!
		$this->dispatch($this->listeners[self::LISTENER_STARTUP], [$this]);

		// Create initial request & response (PSR7!)
		$request = $this->createInitialRequest();
		$response = $this->createInitialResponse();

		try {
			// Trigger event!
			$this->dispatch($this->listeners[self::LISTENER_REQUEST], [$this, $request, $response]);

			// Right to the cycle
			$response = call_user_func(
				$this->chain,
				$request,
				$response,
				function (ServerRequestInterface $request, ResponseInterface $response) {
					return $response;
				}
			);

			// Response validation check
			if (!isset($response) || $response == NULL) {
				throw new RuntimeException('Final response cannot be NULL or unset');
			}
		} catch (Exception $e) {
			// Trigger event! In case of manual handling error, returned object is passed.
			$res = $this->dispatch($this->listeners[self::LISTENER_ERROR], [$this, $e, $request, $response]);
			if ($res !== NULL && $res !== FALSE) return $res;

			// Throw exception again if it's not caught
			if ($this->catchExceptions !== TRUE) throw $e;
		}

		// Trigger event! In case of manual finalizing, returned object is passed.
		$res = $this->dispatch($this->listeners[self::LISTENER_RESPONSE], [$this, $request, $response]);
		if ($res !== NULL && $res !== FALSE) return $res;

		// Send to finalizer (simple send response)
		return $this->finalize($request, $response);
	}

	/**
	 * @return ServerRequestInterface
	 */
	abstract protected function createInitialRequest();

	/**
	 * @return ResponseInterface
	 */
	abstract protected function createInitialResponse();

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 */
	abstract protected function finalize(ServerRequestInterface $request, ResponseInterface $response);

	/**
	 * LISTENERS ***************************************************************
	 */

	/**
	 * @param string $type
	 * @param callable $listener
	 * @return void
	 */
	public function addListener($type, $listener)
	{
		if (!in_array($type, [
			self::LISTENER_STARTUP,
			self::LISTENER_REQUEST,
			self::LISTENER_ERROR,
			self::LISTENER_RESPONSE,
		])) {
			throw new InvalidStateException(sprintf('Given type "%s" is not supported'));
		}

		$this->listeners[$type][] = $listener;
	}

	/**
	 * HELPERS *****************************************************************
	 */

	/**
	 * @param array $handlers
	 * @param array $arguments
	 * @return mixed
	 */
	protected function dispatch(array $handlers, array $arguments)
	{
		// Default return value
		$ret = NULL;

		// Iterate over all events
		foreach ($handlers as $handler) {
			// Take all arguments with last return value
			// and pass to callback handler
			$ret = call_user_func_array($handler, array_merge($arguments, (array) $ret));
		}

		return $ret;
	}

}
