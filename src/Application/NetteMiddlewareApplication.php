<?php

namespace Contributte\Middlewares\Application;

use Contributte\Psr7\Psr7Response;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class NetteMiddlewareApplication extends MiddlewareApplication
{

	/** @var IRequest */
	private $httpRequest;

	/** @var IResponse */
	private $httpResponse;

	/**
	 * @param IRequest $httpRequest
	 * @return void
	 */
	public function setHttpRequest(IRequest $httpRequest)
	{
		$this->httpRequest = $httpRequest;
	}

	/**
	 * @param IResponse $httpResponse
	 * @return void
	 */
	public function setHttpResponse(IResponse $httpResponse)
	{
		$this->httpResponse = $httpResponse;
	}

	/**
	 * @return ServerRequestInterface
	 */
	protected function createInitialRequest()
	{
		return Psr7ServerRequestFactory::fromNette($this->httpRequest);
	}

	/**
	 * @return ResponseInterface
	 */
	protected function createInitialResponse()
	{
		return Psr7ResponseFactory::fromNette($this->httpResponse);
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 */
	protected function finalize(ServerRequestInterface $request, ResponseInterface $response)
	{
		// Act only if it's our Psr7Response
		if ($response instanceof Psr7Response) {
			// And act also only if there is a valid {Nette\Http, Nette\Application} Response
			if ($response->hasHttpResponse() && $response->hasApplicationResponse()) {
				$response->send();

				return $response;
			}
		}

		return parent::finalize($request, $response);
	}

}
