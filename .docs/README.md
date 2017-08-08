## Middlewares / Relay

The middlewares / relay conception is a strong pattern with many benefits.

![Cycle](https://raw.githubusercontent.com/contributte/middlewares/master/.docs/assets/cycle.png)

## Content

- [Installation - how to register an extension](#installation)
- [Modes - nette/standalone mode](#application)
- [Middlewares](#middlewares)
    - [AbstractRootMiddleware](#abstractrootmiddleware)
    - [AutoBasePathMiddleware](#autobasepathmiddleware)
    - [BaseMiddleware](#basemiddleware)
    - [BasePathMiddleware](#basepathmiddleware)
    - [BuilderMiddleware](#buildermiddleware)
    - [PresenterMiddleware](#presentermiddleware)
    - [SecurityMiddleware](#securitymiddleware)
    - [TracyMiddleware](#tracymiddleware)
- [Utils](#utils)
    - [ChainBuilder](#chainbuilder)
    - [Lambda](#lambda)

## Installation

First of all you have to register one of the given extensions ([CompilerExtensions](https://api.nette.org/2.4/Nette.DI.CompilerExtension.html)))in your config file. 
There are basically 2 single modes. 

**Nette mode** is for easy integration to already running projects.

**Standalone mode** is best suitable for new projects with middleware architecture.

```yaml
extensions: 
    # nette mode
    middleware: Contributte\Middlewares\DI\NetteMiddlewareExtension
        
    # standalone mode
    middleware: Contributte\Middlewares\DI\StandaloneMiddlewareExtension
```

## Modes

Main difference to `nette/sandbox` application is in `index.php`. You have to `run` the middleware native `IApplication::run()`. 

```php
$container->getByType(Contributte\Middlewares\Application\IApplication::class)->run();
```

That's all. The main purpose of this is to start via our application, not the default one `Nette\Application\Application`.

### Middlewares

Build your own middleware chain cannot be easier. Just place your middleware (services) under `middleware` section. 
It is pretty same as register new service in `NEON` file.

You can register list of middlewares like this:

```yaml
middleware:
  middlewares:
    # Catch all exceptions
    - Contributte\Middlewares\Middleware\TracyMiddleware
    
    # Your custom middlewares
    - TrailingSlashMiddleware
    - UuidMiddleware
    - CspMiddleware
    
    # Compatibility with Nette applications
    - Contributte\Middlewares\Middleware\PresenterMiddleware
```

Or just register **root** middleware as the very first entrypoint.

```yaml
middleware:
  root: App\Model\AppMiddleware
```

### Ready-to-use middlewares

At this time we have prepared a few middlewares:

#### `AbstractRootMiddleware`

Use should use this middleware if you prefer PHP scripts before NEON declarations.

```php
namespace App;

use Contributte\Middleares\AbstractRootMiddleware;
use Contributte\Middleares\TracyMiddleware;
use Contributte\Middleares\Utils\ChainBuilder;

final class MyAppMiddleware extension AbstractRootMiddleware 
{

    public function create()
    {
        $chain = new ChainBuilder();
        $chain->add(new TracyMiddleware());
        $chain->add(new MyCustomMiddleware());
        
        return $chain->build();
    }

}
```

#### `AutoBasePathMiddleware`

It strips basePath from URL address and pass new URL (without basePath) to next middleware.

```yaml
middleware:
  middlewares:
    # Catch all exceptions
    - Contributte\Middlewares\Middleware\TracyMiddleware
    - Contributte\Middlewares\Middleware\AutoBasePathMiddleware
    
    # Your custom middlewares
    - TrailingSlashMiddleware
    - UuidMiddleware
    - CspMiddleware
```

#### `BaseMiddleware`

This is just abstract class for your middlewares.

```php
namespace App;

use Contributte\Middleares\BaseMiddleware;

final class MyCustomMiddleware extension BaseMiddleware 
{


    /**
     * @param ServerRequestInterface $psr7Request
     * @param ResponseInterface $psr7Response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        // Let's play
    }

}
```

#### `BasePathMiddleware`

It's quite similar with `AutoBasePathMiddleware`, but you could define the basePath.

```yaml
middleware:
  middlewares:
    # Catch all exceptions
    - Contributte\Middlewares\Middleware\TracyMiddleware
    - Contributte\Middlewares\Middleware\BasePathMiddleware(project/www)
    
    # Your custom middlewares
    - TrailingSlashMiddleware
    - UuidMiddleware
    - CspMiddleware
```

#### `BuilderMiddleware`

Over this middleware you can build your own chain of middlewares.

```yaml
middleware:
  middlewares:
    # Catch all exceptions
    - Contributte\Middlewares\Middleware\TracyMiddleware
    - @builder

services:
    builders: 
      class: Contributte\Middlewares\Middleware\BuilderMiddleware
      setup:
        - add(TrailingSlashMiddleware())
        - add(UuidMiddleware())
        - add(CspMiddleware())
```

#### `PresenterMiddleware`

This middleware simulates original nette application behaviours. It converts Psr7Request to `Nette\Application\Request`
and process returned `Nette\Application\Response`.

```yaml
middleware:
  middlewares:
    # Catch all exceptions
    - Contributte\Middlewares\Middleware\TracyMiddleware
    - Contributte\Middlewares\Middleware\PresenterMiddleware
```

#### `TracyMiddleware`

This middleware catch all exceptions and shows tracy dump. It can be instanced normally or via factory `TracyMiddleware::factory($debugMode)`.

```yaml
middleware:
  middlewares:
    tracy1:
      class: Contributte\Middlewares\Middleware\TracyMiddleware
      setup: 
        - enable()
        - setMode(Tracy\Debugger::PRODUCTION)
        - setEmail(cool@contributte.org)

    tracy2:
      class: Contributte\Middlewares\Middleware\TracyMiddleware::factory(%debugMode%)
      setup: 
        - setMode(Tracy\Debugger::PRODUCTION)
        - setEmail(cool@contributte.org)
```

## Utils

### ChainBuilder

```php
$builder = new ChainBuilder();
$builder->add(function ($req, $res, callable $next) {
    return $next($req, $res);
});
$builder->add(function ($req, $res, callable $next) {
    return $next($req, $res);
});
$middleware = $builder->create();
```

### Lambda

Lambda utils class creates anonymous functions.

```php
Lambda::leaf()
// ===
return function (RequestInterface $request, ResponseInterface $response) {
    return $response;
};
```

```php
Lambda::blank()
// ===
return function () {
};
```
