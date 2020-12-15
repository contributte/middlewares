<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Psr7\Psr7Response;
use Contributte\Psr7\Psr7ServerRequest;
use Nette\Application\AbortException;
use Nette\Application\ApplicationException;
use Nette\Application\BadRequestException;
use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\IResponse as IApplicationResponse;
use Nette\Application\Request as ApplicationRequest;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\UI\Presenter;
use Nette\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class PresenterMiddleware implements IMiddleware
{

	/** @var int */
	public static $maxLoop = 20;

	/** @var IPresenterFactory */
	protected $presenterFactory;

	/** @var Router */
	protected $router;

	/** @var ApplicationRequest[] */
	protected $requests = [];

	/** @var IPresenter */
	protected $presenter;

	/** @var string|null */
	protected $errorPresenter;

	/** @var bool */
	protected $catchExceptions = true;

	public function __construct(IPresenterFactory $presenterFactory, Router $router)
	{
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
	}

	public function setErrorPresenter(string $errorPresenter): void
	{
		$this->errorPresenter = $errorPresenter;
	}

	public function setCatchExceptions(bool $catch): void
	{
		$this->catchExceptions = $catch;
	}

	public function getPresenter(): IPresenter
	{
		return $this->presenter;
	}

	/**
	 * @return ApplicationRequest[]
	 */
	public function getRequests(): array
	{
		return $this->requests;
	}

	/**
	 * Dispatch a HTTP request to a front controller.
	 *
	 * @param Psr7ServerRequest|ServerRequestInterface $psr7Request
	 * @param Psr7Response|ResponseInterface           $psr7Response
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next): ResponseInterface
	{
		if (!($psr7Request instanceof Psr7ServerRequest)) {
			throw new InvalidStateException(sprintf('Invalid request object given. Required %s type.', Psr7ServerRequest::class));
		}

		if (!($psr7Response instanceof Psr7Response)) {
			throw new InvalidStateException(sprintf('Invalid response object given. Required %s type.', Psr7Response::class));
		}

		$applicationResponse = null;

		try {
			$applicationResponse = $this->processRequest($this->createInitialRequest($psr7Request));
		} catch (Throwable $e) {
			$errorPresenter = $this->errorPresenter;
			if (!$this->catchExceptions || $errorPresenter === null) {
				throw $e;
			}

			try {
				// Create a new response with given code
				$psr7Response = $psr7Response->withStatus($e instanceof BadRequestException ? ($e->getCode() !== 0 ? $e->getCode() : 404) : 500);
				// Try resolve exception via forward or redirect
				$applicationResponse = $this->processException($e, $errorPresenter);
			} catch (Throwable $e) {
				// No fallback needed
			}

			throw $e;
		}

		$psr7Response = $psr7Response->withApplicationResponse($applicationResponse);

		return $next($psr7Request, $psr7Response);
	}

	public function processRequest(ApplicationRequest $request): IApplicationResponse
	{
		process:
		if (count($this->requests) > self::$maxLoop) {
			throw new ApplicationException('Too many loops detected in application life cycle.');
		}

		$this->requests[] = $request;

		if (!$request->isMethod($request::FORWARD) && !strcasecmp($request->getPresenterName(), (string) $this->errorPresenter)) {
			throw new BadRequestException('Invalid request. Presenter is not achievable.');
		}

		try {
			$this->presenter = $this->presenterFactory->createPresenter($request->getPresenterName());
		} catch (InvalidPresenterException $e) {
			throw count($this->requests) > 1 ? $e : new BadRequestException($e->getMessage(), 0, $e);
		}

		$response = $this->presenter->run(clone $request);

		if ($response instanceof ForwardResponse) {
			// phpcs:ignore
			$request = $response->getRequest();
			goto process;
		}

		return $response;
	}

	/**
	 * @throws ApplicationException
	 * @throws BadRequestException
	 */
	public function processException(Throwable $e, string $errorPresenter): IApplicationResponse
	{
		$args = [
			'exception' => $e,
			'request' => end($this->requests) !== false ? end($this->requests) : null,
		];

		if ($this->presenter instanceof Presenter) {
			try {
				$this->presenter->forward(':' . $errorPresenter . ':', $args);
			} catch (AbortException $foo) {
				return $this->processRequest($this->presenter->getLastCreatedRequest());
			}
		}

		return $this->processRequest(new ApplicationRequest($errorPresenter, ApplicationRequest::FORWARD, $args));
	}

	/**
	 * @throws BadRequestException
	 */
	protected function createInitialRequest(Psr7ServerRequest $psr7Request): ApplicationRequest
	{
		$netteRequest = $psr7Request->getHttpRequest();
		$parameters = $this->router->match($netteRequest);
		$presenter = $parameters[Presenter::PRESENTER_KEY] ?? null;

		if ($presenter === null) {
			throw new InvalidStateException('Missing presenter in route definition.');
		}

		if ($parameters === null || !is_string($presenter)) {
			throw new BadRequestException('No route for HTTP request.');
		}

		try {
			$this->presenterFactory->getPresenterClass($presenter);
		} catch (InvalidPresenterException $e) {
			throw new BadRequestException($e->getMessage(), 0, $e);
		}

		unset($parameters[Presenter::PRESENTER_KEY]);
		return new ApplicationRequest(
			$presenter,
			$netteRequest->getMethod(),
			$parameters,
			$netteRequest->getPost(),
			$netteRequest->getFiles(),
			[ApplicationRequest::SECURED => $netteRequest->isSecured()]
		);
	}

}
