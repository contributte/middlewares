<?php

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tracy\Debugger;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class TracyMiddleware extends BaseMiddleware
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
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		if ($this->enable === TRUE) {
			Debugger::enable($this->mode, $this->logDir, $this->email);
		}

		return $next($psr7Request, $psr7Response);
	}

	/**
	 * FACTORIES ***************************************************************
	 */

	/**
	 * @param bool $debugMode
	 * @return TracyMiddleware
	 */
	public static function factory($debugMode = FALSE)
	{
		$tm = new TracyMiddleware();
		if ($debugMode) $tm->enable();

		return $tm;
	}

}
