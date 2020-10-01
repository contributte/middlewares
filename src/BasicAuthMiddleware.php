<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BasicAuthMiddleware implements IMiddleware
{

	/** @var string */
	private $title;

	/** @var mixed[] */
	private $users = [];

	public function __construct(string $title = 'Restrict zone')
	{
		$this->title = $title;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		$authorization = $this->parseAuthorizationHeader($request->getHeaderLine('Authorization'));
		if ($authorization !== null && $this->auth($authorization['username'], $authorization['password'])) {
			return $next(
				$request->withAttribute('username', $authorization['username']),
				$response
			);
		}

		return $response
			->withStatus(401)
			->withHeader('WWW-Authenticate', 'Basic realm="' . $this->title . '"');
	}

	public function addUser(string $user, string $password, bool $unsecured = false): self
	{
		$this->users[$user] = [
			'password' => $password,
			'unsecured' => $unsecured,
		];
		return $this;
	}

	protected function auth(string $user, string $password): bool
	{
		if (!isset($this->users[$user])) {
			return false;
		}

		return !(
			($this->users[$user]['unsecured'] === true && !hash_equals($password, $this->users[$user]['password'])) ||
			($this->users[$user]['unsecured'] === false && !password_verify($password, $this->users[$user]['password']))
		);
	}

	/**
	 * @return mixed[]|null
	 */
	protected function parseAuthorizationHeader(string $header): ?array
	{
		if (strpos($header, 'Basic') !== 0) {
			return null;
		}

		$header = explode(':', (string) base64_decode(substr($header, 6), true), 2);
		return [
			'username' => $header[0],
			'password' => $header[1] ?? null,
		];
	}

}
