<?php

namespace Contributte\Middlewares\Application;

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

	/** @var callable[] */
	protected $onStartup = [];

	/** @var callable[] */
	protected $onRequest = [];

	/** @var callable[] */
	protected $onError = [];

	/** @var callable[] */
	protected $onResponse = [];

	/** @var callable|IMiddleware */
	private $chain;

	/**
	 * @param callable|IMiddleware $chain
	 */
	public function __construct($chain)
	{
		$this->chain = $chain;
	}

	/**
	 * Dispatch application in middleware cycle!
	 *
	 * @return ResponseInterface
	 */
	public function run()
	{
		// Trigger event!
		$this->dispatch($this->onStartup, [$this]);

		// Create initial request & response (PSR7!)
		$request = $this->createInitialRequest();
		$response = $this->createInitialResponse();

		try {
			// Trigger event!
			$this->dispatch($this->onRequest, [$this, $request, $response]);

			// Right to the cycle
			$response = call_user_func(
				$this->chain,
				$request,
				$response,
				function (ServerRequestInterface $request, ResponseInterface $response) {
					return $response;
				}
			);
		} catch (Exception $e) {
			// Trigger event!
			$this->dispatch($this->onError, [$this, $request, $response, $e]);
		}

		// Response validation check
		if (!isset($response) || $response == NULL) {
			throw new RuntimeException('Final response cannot be NULL or unset');
		}

		// Trigger event!
		$finalize = $this->dispatch($this->onResponse, [$this, $request, $response]);

		// In case of manual finalizing, TRUE breaks next progress..
		if ($finalize === TRUE) return $response;

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
	 * EVENTS ******************************************************************
	 */

	/**
	 * @param callable $callback
	 * @return void
	 */
	public function onStartup(callable $callback)
	{
		$this->onStartup[] = $callback;
	}

	/**
	 * @param callable $callback
	 * @return void
	 */
	public function onRequest(callable $callback)
	{
		$this->onRequest[] = $callback;
	}

	/**
	 * @param callable $callback
	 * @return void
	 */
	public function onError(callable $callback)
	{
		$this->onError[] = $callback;
	}

	/**
	 * @param callable $callback
	 * @return void
	 */
	public function onResponse(callable $callback)
	{
		$this->onResponse[] = $callback;
	}

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
			$ret = call_user_func_array($handlers, $arguments + [$ret]);
		}

		return $ret;
	}

}
