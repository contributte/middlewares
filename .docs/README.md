## Middlewares

The middlewares / relay conception is a strong pattern with many benefits.

![Cycle](https://raw.githubusercontent.com/contributte/middlewares/master/.docs/assets/cycle.png)

## Content

- [Extension - how to register](#extension)
- [Application - nette/standalone mode](#application)
- [Middlewares](#middlewares)
    - [AbstractRootMiddleware](#)
    - [AutoBasePathMiddleware](#)
    - [BaseMiddleware](#)
    - [BasePathMiddleware](#)
    - [ExcludeConditionMiddleware](#)
    - [GroupBuilderMiddleware](#)
    - [GroupMiddleware](#)
    - [PresenterMiddleware](#presentermiddleware)
    - [RouterMiddleware](#)
    - [TracyMiddleware](#tracymiddleware)

## Extension

First of all you have to register one of the given extensions in your config file. 
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

## Application

Main difference to nette/sandbox application is in `index.php`. You have to `run` the middleware native `IApplication::run()`. 

```php
$container->getByType(Contributte\Middlewares\Application\IApplication::class)->run();
```

## Middlewares

Build your own middleware chain cannot be easier. Just place your middleware (services) under `middleware` section. 
It is pretty same as register new service in `NEON` file.

```yaml
middleware:
    # Catch all exceptions
    - Contributte\Middlewares\Middleware\TracyMiddleware
    
    # Your middlewares
    - Custom\Middleware1
    - Custom\Middleware2
    - Custom\Middleware3
    
    # Compatibility with Nette applications
    - Contributte\Middlewares\Middleware\PresenterMiddleware
```

At this time we have prepared a few middlewares:

### `TracyMiddleware`

This middleware catch all exceptions and shows tracy dump.

### `PresenterMiddleware`

This middleware simulates original nette application behaviours. It converts Psr7Request to `Nette\Application\Request`
and process returned `Nette\Application\Response`.
