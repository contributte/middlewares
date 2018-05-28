<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Application;

use Contributte\Psr7\Psr7Response;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NetteMiddlewareApplication extends MiddlewareApplication
{

	/** @var IRequest|null */
	private $httpRequest;

	/** @var IResponse|null */
	private $httpResponse;

	public function setHttpRequest(IRequest $httpRequest): void
	{
		$this->httpRequest = $httpRequest;
	}

	public function setHttpResponse(IResponse $httpResponse): void
	{
		$this->httpResponse = $httpResponse;
	}

	protected function createInitialRequest(): ServerRequestInterface
	{
		if ($this->httpRequest !== null) {
			return Psr7ServerRequestFactory::fromNette($this->httpRequest);
		}

		return Psr7ServerRequestFactory::fromGlobal();
	}

	protected function createInitialResponse(): ResponseInterface
	{
		if ($this->httpResponse !== null) {
			return Psr7ResponseFactory::fromNette($this->httpResponse);
		}

		return Psr7ResponseFactory::fromGlobal();
	}

	protected function finalize(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		// Act only if it's our Psr7Response and only if there is a valid {Nette\Http, Nette\Application} Response
		if ($response instanceof Psr7Response && $response->hasHttpResponse() && $response->hasApplicationResponse()) {
			$response->send();

			return $response;
		}

		return parent::finalize($request, $response);
	}

}
