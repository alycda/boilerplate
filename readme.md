PHP/HTML5 Boilerplate with CMS
===

* Source: [https://github.com/alycda/boilerplate](https://github.com/alycda/boilerplate)

---

+ **Author:** Chris Neal	<chris[at]tophcreative[dot]com>
+ **Editor:** Alyssa Davis	<hello[at]alyda[dot]me>
+ **Last Update:** May 6th, 2013
+ **Version:** 0.0.1 _imperfect, but a start_

## Quick start

Install with Bower — `bower install https://github.com/alycda/boilerplate.git`

## Features

* HTML5 using [Bootstrap](http://twitter.github.io/bootstrap/).
* Cross-browser compatible (Chrome, Opera, Safari, Firefox 3.6+, IE6+).
* Includes [Normalize.css](http://necolas.github.com/normalize.css/) for CSS
  normalizations and common bug fixes.
* The latest [jQuery](http://jquery.com/) via CDN, with a local fallback.
* IE-specific classes for easier cross-browser control.
* Placeholder CSS Media Queries.
* Useful CSS helpers.
* Default print CSS, performance optimized.
* An optimized Google Analytics snippet.
* Apache server caching, compression, and other configuration defaults for
  Grade-A performance.
* Accompanying documentation.

##Getting started

* [Usage](doc/usage.md) — Overview of the project contents.
* [FAQ](doc/faq.md) — Frequently asked questions, along with their answers.
* chmod 777 folders (if necessary)
* .htaccess
	* modify directives as needed
* phpmyadmin
	* create database
	* import cms.sql
* &#95;includes/config.php
	* localhost
	* database name
	* un/pw
	* $PATH_FROM_ROOT (`/` | `/_boilerplate/` )
	* $URL&#95;BASE (`http://localhost` | `http://boilerplate`)	

##The core of PHP/HTML5 Boilerplate with CMS

* [HTML](doc/html.md) — A guide to the default HTML.
* [CSS](doc/css.md) — A guide to the default CSS.
* [JavaScript](js.md) — A guide to the default JavaScript.
* [.htaccess](https://github.com/h5bp/server-configs/blob/master/apache/README.md)
  — All about the Apache web server config.
* [Everything else](misc.md).

## Development

* [Extending and customizing HTML5 Boilerplate](doc/extend.md) — Going further with
  the boilerplate.

---

**fin**

* turn off php warnings & errors on live site (_includes/application_top.php)
* edit humans.txt
