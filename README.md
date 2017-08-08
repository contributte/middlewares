# Middlewares

:boom: Middleware / Relay / PSR-7 support to [`Nette Framework`](https://github.com/nette).

-----

[![Build Status](https://img.shields.io/travis/contributte/middlewares.svg?style=flat-square)](https://travis-ci.org/contributte/middlewares)
[![Code coverage](https://img.shields.io/coveralls/contributte/middlewares.svg?style=flat-square)](https://coveralls.io/r/contributte/middlewares)
[![Licence](https://img.shields.io/packagist/l/contributte/middlewares.svg?style=flat-square)](https://packagist.org/packages/contributte/middlewares)

[![Downloads this Month](https://img.shields.io/packagist/dm/contributte/middlewares.svg?style=flat-square)](https://packagist.org/packages/contributte/middlewares)
[![Downloads total](https://img.shields.io/packagist/dt/contributte/middlewares.svg?style=flat-square)](https://packagist.org/packages/contributte/middlewares)
[![Latest stable](https://img.shields.io/packagist/v/contributte/middlewares.svg?style=flat-square)](https://packagist.org/packages/contributte/middlewares)

## Discussion / Help

[![Join the chat](https://img.shields.io/gitter/room/contributte/contributte.svg?style=flat-square)](http://bit.ly/ctteg)

## Install

```
composer require contributte/middlewares
```

## Versions

| State       | Version | Branch   | PHP      |
|-------------|---------|----------|----------|
| development | `^0.2`  | `master` | `>= 5.6` |

## Prolog

Middleware / Relay pattern is widely used for handling any HTTP requests, such as API request, streams, dynamic websites 
or just any suitable requests.

We have a many solutions and prepared libraries in PHP world. 

3rd party middlewares:

- [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares) - biggest collection of PHP middlewares
- [stackphp](https://github.com/stackphp) - connect middleware pattern and symfony HttpKernel
- [zendframework/zend-diactoros](https://github.com/zendframework/zend-diactoros/) - Zend PSR-7 middleware

## Overview

- [Installation - how to register an extension](https://github.com/contributte/middlewares/tree/master/.docs#installation)
- [Modes - nette/standalone mode](https://github.com/contributte/middlewares/tree/master/.docs#modes)
- [Middlewares - implementations](https://github.com/contributte/middlewares/tree/master/.docs#middlewares)
- [Utils - useful classes](https://github.com/contributte/middlewares/tree/master/.docs#utils)

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
  <tbody>
</table>

-----

The development is sponsored by [Tlapnet](http://www.tlapnet.cz). Thank you guys! :+1:

-----

Thank you for testing, reporting and contributing.
