<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Application;

use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareApplication extends AbstractApplication
{

	protected function createInitialRequest(): ServerRequestInterface
	{
		return Psr7ServerRequestFactory::fromGlobal();
	}

	protected function createInitialResponse(): ResponseInterface
	{
		return Psr7ResponseFactory::fromGlobal();
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
		header(sprintf('HTTP/%s %s %s', $version, $status, $phrase));
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
		foreach ($values as $value) {
			// never send multiple content-type headers
			if (preg_match('/content-type/i', $name)) {
				header(sprintf('%s: %s', $name, $value));
			}
			else {
				header(sprintf('%s: %s', $name, $value), false);
			}
		}
	}

	protected function sendBody(ResponseInterface $response): void
	{
		$stream = $response->getBody();
		$stream->rewind();
		while (!$stream->eof()) {
			echo $stream->read(8192);
		}
	}

}
