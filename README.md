# Contributte Middlewares

Middleware / Relay / PSR-7 support to [`Nette Framework`](https://github.com/nette).

[![Build Status](https://img.shields.io/travis/contributte/middlewares.svg?style=flat-square)](https://travis-ci.org/contributte/middlewares)
[![Code coverage](https://img.shields.io/coveralls/contributte/middlewares.svg?style=flat-square)](https://coveralls.io/r/contributte/middlewares)
[![Licence](https://img.shields.io/packagist/l/contributte/middlewares.svg?style=flat-square)](https://packagist.org/packages/contributte/middlewares)
[![Downloads this Month](https://img.shields.io/packagist/dm/contributte/middlewares.svg?style=flat-square)](https://packagist.org/packages/contributte/middlewares)
[![Downloads total](https://img.shields.io/packagist/dt/contributte/middlewares.svg?style=flat-square)](https://packagist.org/packages/contributte/middlewares)
[![Latest stable](https://img.shields.io/packagist/v/contributte/middlewares.svg?style=flat-square)](https://packagist.org/packages/contributte/middlewares)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)

## Discussion / Help

[![Join the chat](https://img.shields.io/gitter/room/contributte/contributte.svg?style=flat-square)](http://bit.ly/ctteg)

## Prolog

Middleware / Relay pattern is widely used for handling any HTTP requests, such as API request, streams, dynamic websites
or just any suitable requests.

We have a many solutions and prepared libraries in PHP world.

3rd party middlewares:

- [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares) - biggest collection of PHP middlewares
- [stackphp](https://github.com/stackphp) - connect middleware pattern and symfony HttpKernel
- [zendframework/zend-diactoros](https://github.com/zendframework/zend-diactoros/) - Zend PSR-7 middleware

## Documentation

- [Setup](.docs/README.md#setup)
- [Modes - nette/standalone mode](.docs/README.md#modes)
- [Application - life cycle](.docs/README.md#application)
- [Middlewares](.docs/README.md#middlewares)
- [Utils](.docs/README.md#utils)
- [Playground](.docs/README.md#playground)

## Versions

| State  | Version      | Branch   | Nette  | PHP     |
|--------|--------------|----------|--------|---------|
| dev    | `^0.10.0`    | `master` | `3.0+` | `^7.2`  |
| stable | `^0.9.0`     | `master` | `3.0+` | `^7.2`  |
| stable | `^0.8.0`     | `master` | `2.4`  | `>=7.1` |
| stable | `^0.5.0`     | `master` | `2.4`  | `>=5.6` |

## Design

![Cycle](https://raw.githubusercontent.com/contributte/middlewares/master/.docs/assets/cycle.png)

## Maintainers

<table>
  <tbody>
    <tr>
      <td align="center">
        <a href="https://github.com/f3l1x">
            <img width="150" height="150" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=150">
        </a>
        </br>
        <a href="https://github.com/f3l1x">Milan Felix Šulc</a>
      </td>
    </tr>
  </tbody>
</table>

The development is sponsored by [Tlapnet](http://www.tlapnet.cz). Thank you guys! :+1:

Thank you for testing, reporting and contributing.
