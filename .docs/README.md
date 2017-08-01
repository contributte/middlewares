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
    - TrainlingSlashMiddleware
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

@todo

#### `AutoBasePathMiddleware`

@todo

#### `BaseMiddleware`

@todo

#### `BasePathMiddleware`

@todo

#### `BuilderMiddleware`

@todo

#### `PresenterMiddleware`

This middleware simulates original nette application behaviours. It converts Psr7Request to `Nette\Application\Request`
and process returned `Nette\Application\Response`.

#### `TracyMiddleware`

This middleware catch all exceptions and shows tracy dump.
