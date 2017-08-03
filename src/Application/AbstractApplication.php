<?php

namespace Contributte\Middlewares\Application;

use Contributte\Middlewares\IMiddleware;
use Exception;
use Nette\SmartObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 *
 * @method void onStartup(AbstractApplication $self)
 * @method void onRequest(AbstractApplication $self, ServerRequestInterface $req, ResponseInterface $res)
 * @method void onError(AbstractApplication $self, Exception $e, ServerRequestInterface $req, ResponseInterface $res)
 * @method ResponseInterface onResponse(AbstractApplication $self, ServerRequestInterface $req, ResponseInterface $res)
 */
abstract class AbstractApplication implements IApplication
{

	use SmartObject;

	/** @var callable[] */
	public $onStartup = [];

	/** @var callable[] */
	public $onRequest = [];

	/** @var callable[] */
	public $onError = [];

	/** @var callable[] */
	public $onResponse = [];

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
		$this->onStartup($this);

		// Create initial request & response (PSR7!)
		$request = $this->createInitialRequest();
		$response = $this->createInitialResponse();

		try {
			// Trigger event!
			$this->onRequest($this, $request, $response);

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
			$this->onError($this, $e, $request, $response);
		}

		// Response validation check
		if (!isset($response) || $response == NULL) {
			throw new RuntimeException('Final response cannot be NULL or unset');
		}

		// Trigger event!
		$finalize = $this->onResponse($this, $request, $response);

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

}
