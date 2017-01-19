<?php

namespace Contributte\Middlewares\Middleware;

use Contributte\Psr7\Psr7Response;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Tracy\Debugger;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class TracyMiddleware
{

	/** @var bool */
	protected $enable = FALSE;

	/** @var mixed */
	protected $mode = Debugger::DEVELOPMENT;

	/** @var string */
	protected $logDir;

	/** @var string */
	protected $email;

	/**
	 * @param mixed $mode
	 * @param string $logDir
	 * @param string $email
	 */
	public function __construct($mode = Debugger::DEVELOPMENT, $logDir = NULL, $email = NULL)
	{
		$this->mode = $mode;
		$this->logDir = $logDir;
		$this->email = $email;
	}

	/**
	 * @return void
	 */
	public function enable()
	{
		$this->enable = TRUE;
	}

	/**
	 * @return void
	 */
	public function disable()
	{
		$this->enable = FALSE;
	}

	/**
	 * @param mixed $mode
	 * @return void
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
	}

	/**
	 * @param string $logDir
	 * @return void
	 */
	public function setLogDir($logDir)
	{
		$this->logDir = $logDir;
	}

	/**
	 * @param string $email
	 * @return void
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * Catch all exceptions
	 *
	 * @param RequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return Psr7Response
	 */
	public function __invoke(RequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		if ($this->enable === TRUE) {
			Debugger::enable($this->mode, $this->logDir, $this->email);
		}

		try {
			// Pass to next middleware
			$psr7Response = $next($psr7Request, $psr7Response);
		} catch (Throwable $e) {
			// Handle is followed
		} catch (Exception $e) {
			// Handle is followed
		}

		if (isset($e)) {
			Debugger::exceptionHandler($e, TRUE);
		}

		return $psr7Response;
	}

}
