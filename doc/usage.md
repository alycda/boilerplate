[Go back](../start.md)

# Usage

Once you have cloned or downloaded HTML5 Boilerplate, creating a site or app
usually involves the following:

1. Set up the basic structure of the site.
2. Add some content, style, and functionality.
3. Run your site locally to see how it looks.
4. (Optionally run a build script to automate the optimization of your site -
   e.g. [ant build script](https://github.com/h5bp/ant-build-script) or [node
   build script](https://github.com/h5bp/node-build-script)).
5. Deploy your site.


## Basic structure

A basic site initially looks like this:

<pre>
.
├── _files
├── _images
│   ├── icons
│   │   └── [apple-touch-icons]
│   └── original
├── _includes
│   ├── application_bottom.php
│   ├── application_top.php
│   ├── config.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── sql
│       ├── cms.sql
│       ├── state.sql
│       └── stores.sql
├── _templates
│   ├── home.php
│   └── sitemap.php
├── .htaccess
├── 6bFQM3N9IqgAHoP
├── 52Eb0wNACqfUcuD
├── 404.html
├── apple-touch-icon.png
├── cmsadmin
│   └── [files]
├── css
│   ├── bootstrap-responsive.css
│   ├── bootstrap-responsive.min.css
│   ├── bootstrap.css
│   ├── bootstrap.min.css
│   ├── img
│   │   ├── glyphicons-halflings-white.png
│   │   └── glyphicons-halflings.png
│   ├── normalize.css
│   └── style.css
├── doc
│   └── [files]
├── favicon.ico
├── humans.txt
├── index.php
├── js
│   ├── custom.js
│   └── vendor
│       ├── bootstrap.js
│       ├── bootstrap.min.js
│       └── jquery-latest.min.js
├── readme.md
└── robots.txt
</pre>

What follows is a general overview of each major part and how to use them.

### css

This directory should contain all your project's CSS files. It includes some
initial CSS to help get you started from a solid foundation. [About the
CSS](css.md).

### doc

This directory contains all the Boilerplate documentation. You can use it
as the location and basis for your own project's documentation.

### js

This directory should contain all your project's JS files. Libraries, plugins,
and custom code can all be included here. [About the JavaScript](js.md).

### .htaccess

The default web server config is for Apache. **Broken Link:** [About the .htaccess](htaccess.md).

### 404.html

A helpful custom 404 to get you started.

### index.html

This is the default page that all requests will be redirected to. This is the basis for 'Pretty Urls'.

If you are using Google Analytics, make sure that you edit the corresponding
snippet at the bottom to include your analytics ID.

### humans.txt

Edit this file to include the team that worked on your site/app, and the
technology powering it.

### robots.txt

Edit this file to include any pages you need hidden from search engines.

### icons

Replace the default `favicon.ico` and apple touch icons with your own. Check out Hans Christian's handy [HTML5 Boilerplate Favicon and Apple Touch Icon PSD-Template](http://drublic.de/blog/html5-boilerplate-favicons-psd-template/).
