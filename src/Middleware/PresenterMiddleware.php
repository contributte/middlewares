<?php

namespace Contributte\Middlewares\Middleware;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Psr7\Psr7Request;
use Contributte\Psr7\Psr7Response;
use Contributte\Psr7\Psr7ServerRequest;
use Exception;
use Nette\Application\AbortException;
use Nette\Application\ApplicationException;
use Nette\Application\BadRequestException;
use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\IResponse as IApplicationResponse;
use Nette\Application\IRouter;
use Nette\Application\Request as ApplicationRequest;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\UI\Presenter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class PresenterMiddleware extends BaseMiddleware
{

	/** @var int */
	public static $maxLoop = 20;

	/** @var IPresenterFactory */
	protected $presenterFactory;

	/** @var IRouter */
	protected $router;

	/** @var ApplicationRequest[] */
	protected $requests = [];

	/** @var IPresenter */
	protected $presenter;

	/** @var string */
	protected $errorPresenter;

	/** @var bool */
	protected $catchExceptions = TRUE;

	/**
	 * @param IPresenterFactory $presenterFactory
	 * @param IRouter $router
	 */
	public function __construct(IPresenterFactory $presenterFactory, IRouter $router)
	{
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
	}

	/**
	 * @param string $errorPresenter
	 * @return void
	 */
	public function setErrorPresenter($errorPresenter)
	{
		$this->errorPresenter = $errorPresenter;
	}

	/**
	 * @param boolean $catch
	 * @return void
	 */
	public function setCatchExceptions($catch)
	{
		$this->catchExceptions = boolval($catch);
	}

	/**
	 * @return IPresenter
	 */
	public function getPresenter()
	{
		return $this->presenter;
	}

	/**
	 * @return ApplicationRequest[]
	 */
	public function getRequests()
	{
		return $this->requests;
	}

	/**
	 * Dispatch a HTTP request to a front controller.
	 *
	 * @param Psr7ServerRequest $psr7Request
	 * @param Psr7Response $psr7Response
	 * @param callable $next
	 * @return Psr7Response
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		if (!($psr7Request instanceof Psr7ServerRequest)) {
			throw new InvalidStateException(sprintf('Invalid request object given. Required %s type.', Psr7ServerRequest::class));
		}

		if (!($psr7Response instanceof Psr7Response)) {
			throw new InvalidStateException(sprintf('Invalid response object given. Required %s type.', Psr7Response::class));
		}

		$applicationResponse = NULL;

		try {
			$applicationResponse = $this->processRequest($this->createInitialRequest($psr7Request));
		} catch (Throwable $e) {
			// Handle is followed
		} catch (Exception $e) {
			// Handle is followed
		}

		if (isset($e)) {
			if (!$this->catchExceptions || !$this->errorPresenter) {
				throw $e;
			}

			try {
				// Create a new response with given code
				$psr7Response = $psr7Response->withStatus($e instanceof BadRequestException ? ($e->getCode() ?: 404) : 500);
				// Try resolve exception via forward or redirect
				$applicationResponse = $this->processException($e);
			} catch (Throwable $e) {
				// No fallback needed
			} catch (Exception $e) {
				// No fallback needed
			}
		}

		// Convert to Psr7Response
		if ($applicationResponse instanceof Psr7Response) {
			// If response is Psr7Response type, just use it
			$psr7Response = $applicationResponse;
		} else if ($applicationResponse instanceof IApplicationResponse) {
			// If response is IApplicationResponse, wrap to Psr7Response
			$psr7Response = $psr7Response->withApplicationResponse($applicationResponse);
		} else {
			throw new InvalidStateException();
		}

		// Pass to next middleware
		$psr7Response = $next($psr7Request, $psr7Response);

		// Return response
		return $psr7Response;
	}

	/**
	 * @param Psr7ServerRequest|Psr7Request $psr7Request
	 * @return ApplicationRequest
	 */
	protected function createInitialRequest($psr7Request)
	{
		$request = $this->router->match($psr7Request->getHttpRequest());

		if (!$request instanceof ApplicationRequest) {
			throw new BadRequestException('No route for HTTP request.');

		} elseif (strcasecmp($request->getPresenterName(), $this->errorPresenter) === 0) {
			throw new BadRequestException('Invalid request. Presenter is not achievable.');
		}

		try {
			$name = $request->getPresenterName();
			$this->presenterFactory->getPresenterClass($name);
		} catch (InvalidPresenterException $e) {
			throw new BadRequestException($e->getMessage(), 0, $e);
		}

		return $request;
	}

	/**
	 * @param ApplicationRequest $request
	 * @return IApplicationResponse
	 */
	public function processRequest(ApplicationRequest $request)
	{
		process:
		if (count($this->requests) > self::$maxLoop) {
			throw new ApplicationException('Too many loops detected in application life cycle.');
		}

		$this->requests[] = $request;
		$this->presenter = $this->presenterFactory->createPresenter($request->getPresenterName());
		$response = $this->presenter->run(clone $request);

		if ($response instanceof ForwardResponse) {
			$request = $response->getRequest();
			goto process;
		}

		if ($response == NULL) {
			throw new BadRequestException('Invalid response. Nullable.');
		}

		return $response;
	}

	/**
	 * @param Exception|Throwable $e
	 * @return IApplicationResponse
	 */
	public function processException($e)
	{
		$args = [
			'exception' => $e,
			'request' => end($this->requests) ?: NULL,
		];

		if ($this->presenter instanceof Presenter) {
			try {
				$this->presenter->forward(':' . $this->errorPresenter . ':', $args);
			} catch (AbortException $foo) {
				return $this->processRequest($this->presenter->getLastCreatedRequest());
			}
		}

		return $this->processRequest(new ApplicationRequest($this->errorPresenter, ApplicationRequest::FORWARD, $args));
	}

}
