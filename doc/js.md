[Go back](../start.md)

# The JavaScript

Information about the default JavaScript included in the project.

## custom.js

This file can be used to contain or reference your site/app JavaScript code.
For larger projects, you can make use of a JavaScript module loader, like
[Require.js](http://requirejs.org/), to load any other scripts you need to
run.

One approach is to put jQuery plugins inside of a `(function($){ ...
})(jQuery);` closure to make sure they're in the jQuery namespace safety
blanket. Read more about [jQuery plugin
authoring](http://docs.jquery.com/Plugins/Authoring#Getting_Started)

---

You may wish to create your own [custom Modernizr
build](http://www.modernizr.com/download/).
