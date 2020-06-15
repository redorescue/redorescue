jQuery-Validation-Bootstrap-tooltip
===================================

A drop in extension replacing error labels from jQuery Validation plugin with Twitter Bootstrap tooltips

Requirements
-------------
* [jQuery](http://jquery.com/)
* [jQuery Validation](http://jqueryvalidation.org/)
* [Twitter Bootstrap](http://getbootstrap.com/) (Tested with v2.3.2, v3.3.5 and 4.0.0alpha)

Usage
------
Tooltip options are given either through an element's data attributes or as objects set during validate initializing.  An example would be:

        $("#theform").validate({
            rules: {
               thefield: { digits:true, required: true }
            },
            tooltip_options: {
               thefield: { placement: 'left' }
            }
         });

ASP MVC developers
-------
There is an equivalent project that caters to jQuery Validation Unobtrusive, [johnnyreilly/jQuery.Validation.Unobtrusive.Native](https://github.com/johnnyreilly/jQuery.Validation.Unobtrusive.Native) and also provides [tooltip errors](http://johnnyreilly.github.io/jQuery.Validation.Unobtrusive.Native/AdvancedDemo/Tooltip.html)

Changelog
-----
* 0.10.2 - Use Boostrap defined defaults.  Thanks to [fernandoluizao](https://github.com/fernandoluizao)
* 0.10.1 - Add missing dispose for Bootstrap 4.0.0 alpha.  Thanks to [p34eu](https://github.com/p34eu)
* 0.10.0 - Merged flickerfix and Bootstrap 4.0.0 alpha update into single script.
* 0.9.1 - Corrected '_all_' option in flickerfix version.
* 0.9.0 - Corrected selector data parameter Thanks to [QN-Solutions](https://github.com/QN-Solutions)
* 0.8.0 - Properly remove error class when valid
* 0.7.1 - Created flickerfix version for Bootstrap 3.3.0 and up  Thanks to [luis226](https://github.com/luis226). Linted.
* 0.7 -   Added ability to apply options to all elements. Thanks to [thiagof](https://github.com/thiagof)
* 0.6 -   Corrected default selector option
* 0.5 -   Fixed missing highlight/unhighlight calls
* 0.4 -   Fixed missing toggle of error/valid class on input element  
* 0.3 -   Fixed IE 7/8 error caused but the rouge trim function
* 0.2 -   Added extra error check and added minified version.
* 0.1 -   Inital release.

Demo
-----
[Demo](http://thrilleratplay.github.io/jquery-validation-bootstrap-tooltip/) or it didn't happen

* * *
###### Special Thanks to dennysfredericci
Who's gist ([found here](https://gist.github.com/dennysfredericci/3030983))was the basis of this extension.
