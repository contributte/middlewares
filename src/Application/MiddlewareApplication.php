<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Application;

use Contributte\Middlewares\Application\AbstractApplication;
use Contributte\Psr7\Psr7Request;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Contributte\Psr7\Psr7UriFactory;
use GuzzleHttp\Psr7\Utils;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Request as NetteRequest;
use Nette\Http\Response as NetteResponse;

use Nette\Http\RequestFactory;
use Nette\Http\UrlScript;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleHttpRequest;

use Swoole\Http\Response as SwooleHttpResponse;


class MiddlewareApplication extends AbstractApplication
{
	private $swooleRequest;
	private $swoole = false;

	private $swooleResponse;

	private const UNIQUE_HEADERS = [
		'content-type',
	];


	/**
	 * Dispatch application!
	 *
	 * @return string|int|bool|void|ResponseInterface|null
	 */
	public function run(?SwooleHttpRequest $request=null,?SwooleHttpResponse $response=null)
	{
		if (($request) && ($response))
		{
			$this->swooleRequest = $request;
			$this->swooleResponse = $response;
			$this->swoole = true;
		}

		return parent::run();		
	}



	protected function createInitialRequest(): ServerRequestInterface
	{

		if ($this->swoole){			
			$netteRequest = $this->createNetteHttpRequest($this->swooleRequest);
			return Psr7ServerRequestFactory::fromNette($netteRequest);
		}
		return Psr7ServerRequestFactory::fromGlobal();

	}


	function createNetteHttpRequest(SwooleHttpRequest $swooleRequest): IRequest
	{
		$url = new UrlScript($swooleRequest->server['request_uri']);

		$httpRequest = new NetteRequest($url, $swooleRequest->post, $swooleRequest->files, $swooleRequest->cookie, $swooleRequest->header, $swooleRequest->getMethod(),$swooleRequest->server['remote_addr'],$swooleRequest->server['remote_host']);
		
		return $httpRequest;
	}


	protected function createInitialResponse(): ResponseInterface
	{
		if ($this->swoole){
			$netteResponse = $this->createNetteHttpResponse($this->swooleResponse);
			return Psr7ResponseFactory::fromNette($netteResponse);
		}
		return Psr7ResponseFactory::fromGlobal();

	}

	protected function createNetteHttpResponse(SwooleHttpResponse $swooleResponse):?NetteResponse
	{
		//TODO add som functionality if needed
		$httpResponse = new NetteResponse();

		return $httpResponse;
	}




	protected function finalize(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$this->sendStatus($response);
		$this->sendHeaders($response);
		$this->sendBody($response);

		return $response;
	}

	protected function sendStatus(ResponseInterface $response): void
	{
		$version = $response->getProtocolVersion();
		$status = $response->getStatusCode();
		$phrase = $response->getReasonPhrase();
		if ($this->swoole)
		{

			$this->swooleResponse->status($status,$phrase);
		}
		else
		{
			header(sprintf('HTTP/%s %s %s', $version, $status, $phrase));
		}


	}

	protected function sendHeaders(ResponseInterface $response): void
	{
		foreach ($response->getHeaders() as $name => $values) {
			$this->sendHeader($name, $values);
		}
	}


	/**
	 * @param string[] $values
	 */
	protected function sendHeader(string $name, array $values): void
	{
		$name = str_replace('-', ' ', $name);
		$name = ucwords($name);
		$name = str_replace(' ', '-', $name);
		$replace = in_array(strtolower($name), self::UNIQUE_HEADERS, true);
		foreach ($values as $value) {

			if ($this->swoole){
				$this->swooleResponse->header($name,$value); //FIXME is sprintf needed in this case?
			}
			else {
				header(sprintf('%s: %s', $name, $value), $replace); 
			}
		
		}


	}


	protected function sendBody(ResponseInterface $response): void
	{
		$stream = $response->getBody();
		$stream->rewind();

		if ($this->swoole)
		{
			while (!$stream->eof()) {
				$this->swooleResponse->write($stream->read(8192));
				echo $stream->read(8192);
			}
		}
		else
		{
			while (!$stream->eof()) {
				echo $stream->read(8192);
			}
	
		}
	}


}
