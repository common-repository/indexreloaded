=== IndexReloaded ===
Contributors: giselewendl
Donate link: https://www.toctoc.ch/
Link to privacy policy: https://www.toctoc.ch/en/privacy-policy/
Tags: CSS, Optimization, JavaScript
Requires at least: 4.7
Tested up to: 6.6
Stable tag: 1.2.1
Requires PHP: 7.0
License: GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Get warp 10 for page speed performance with IndexReloaded: Minify CSS and JS, defer CSS and JS, clean up HTML, generate critical CSS and more!

== Description ==

**IndexReloaded** is the ultimate performance plugin, specially designed to tackle slow loading times on your website on both desktop and mobile platforms. This plugin addresses Google PageSpeed Insights issues and significantly improves Core Web Vitals.

This plugin offers a range of features designed to optimize your website's speed:

- **Resolve Render-Blocking Issues:** Address render-blocking CSS and JavaScript issues to streamline loading.
- **Enhancing Critical Rendering Path:** Automatically generate critical CSS for above-the-fold content to improve the critical rendering path.
- **Minification:** Reduce load size by minifying JavaScript, and CSS files.
- **Resource Bundling:** Combine inline/external JavaScript and inline/external CSS to reduce server requests.
- **Deferred Loading:** Prioritize content rendering by deferring CSS and JavaScript loading.
- **Cache Leveraging:** Utilize server-side object-cache for improved performance.
- **Host external CSS or JS locally:** For improved performance hosting external CSS and JS on your server is a big help.

This plugin is your go-to solution for reducing slow loading times, improving SEO and boosting website speed, making it an essential tool for performance optimization and SEO.

### Why Choose IndexReloaded?

Are you looking to improve your website's performance? IndexReloaded excels in core web vitals. IndexReloaded represents our expertise gained from optimizing the performance of plenty of websites on mobile devices. We believe you could well find a similar, user-friendly, all-in-one solution for boosting the performance your website. But we do not believe that you find similar honesty and better price-performance relation in similar products.

Benefit from our suite of unique features designed to turbocharge your site's loading speed. From innovative critical CSS generation for above-the-fold content to implementation of tagged page caching, we ensure lightning-fast load times that improve critical performance metrics such as Largest Contentful Paint (LCP), Cumulative Layout Shift (CLS), and more.

### Before You Install

Our statistics indicate that the plugin improves the speed of 4 out of 5 websites. However, certain theme and plugin combinations, especially those related to caching and optimization, may lead to compatibility issues. Therefore, our plugin might not suit every website. To preview how IndexReloaded could benefit your site, there's a simple tool that allows you to test it before installing it. **We highly recommend** that you visit [PageSpeed Insights](https://pagespeed.web.dev/) and run a test of your website beforehand. Please note: To accurately test your site on PageSpeed Insights, it's crucial to temporarily disable any optimizing plugins. This test requires raw data to apply its own optimization.

### Features

#### Licencing

The free version, run without licence key has everything enabled apart form CCSS (critical CSS) creation. On our website TocToc Internetmanagement (https://www.toctoc.ch/en/getindexloaded) we offer a free licence key valid for one site and one month - which allows to run a full version of the plugin before you decide if it's worth around 30 CHF a year (for 3 websites).

IndexReloaded offers 3 different licences:
- **Free**: suitable for testing IndexReloaded for one month,
- **Standard**: enables CCSS on 3 sites for a year,
- **Developer**: enables CCSS on 12 sites for a year

Privacy information: The free version of IndexReloaded does not any connection to third party websites, like ours - toctoc.ch. The licenced version connects to our website toctoc.ch for licence validation, the connection is strictly restraint to this purpose and does not send data outside the scope of licence validation to toctoc.ch.

#### IndexReloaded Backend

The backend is divided in two menu items, the "Overview" and the "Settings".

For correct basic operation of IndexReloaded it will be necessary to identify JavaScript which IndexReloaded should exclude for its operations like regrouping, compression and defered loading). In some cases also CSS must get excluded.
We supply an experience-based database that helps to indentify recommended excludes and - if some are found - adds them to the IndexReloaded Excludes list for your site - based on theme, plugins and elements found in your pages.
This experience-based database is distributed along every release of IndexReloaded and licenced users can download a fresh copy of the experience-based database from our site in the backend of Indexreloaded in Overview->Recommended excludes

#### IndexReloaded Overview

In "Overview" we first focus on the basic operation of the plugin and on the challenge we have to solve to make it work properly.
After this we have 3 tabs where the overall state of the plugin is displayed. You see if it is active and see the licencing state.

In tab "CSS and JS files" you see information on the css and js files produced by indexreloaded. With the "Delete files"-button you can remove all files on disk and force IndexReloaded to recreate the files.
Tab "Licence key" informs on the used licence and its validity.
Tab "Exclude list", shows the proposed excludes for your settings "CSS and JS processing" setting "Exclude list" and delivers informations for the reasons why the excludes are recommended. These information come from the experience-based database.

#### The experience-based database
As we are on release 1.1.1. and this is valid for all subsequent versions, the experience behind the experience-based database *must still grow*. It is important to us to get your feedback on excludes you find in your site and which are not part yet of our experience-based database. So please send us your finds, along with plugins or themes concerned, to our moderated e-mail address knowledgebase@toctoc.ch

#### IndexReloaded Settings

IndexReloaded organizes its settings into groups for general settings, settings related to CSS and JS processing, to Critical CSS, HTML-cleanup, preloading and monitoring.

#### General settings

Within General settings, you can add a licence-key and setup some basic behaviors of the plugin, like where to store css and js-files, days generated files stay on disk and more. With setting "Activation" you activate or desactivate the enire frontend processing of IndexReloaded.


#### CSS and JS processing 

The first part of these settings concern what IndexReloaded processes regarding CSS and JavaScript. You can turn off everything, turn off CSS or JavaScript-processing individually.
As the core setting, the "Exclude list" allows to identify JavaScript and CSS that must be left intact by IndexReloaded. It's a core setting because here you can make all possible errors disappear by add correct identifiers. 
The identifiers in this comma-separated list can be for example the id of a script or part of a filename, for inline JavaScript even parts of the code may be used as identifier.
Setting "Force new files" is handy when developing the "Excludes list", alternatively one could delete all files in "Overview".
With "Production mode" all 3 URL-Parameters we can pass along the request form the frontend, will be disabled.
With "Load last JS file asynchronous" one can either defer or not defer the JavaScript-files created by IndexReloaded.
Even after he grouping of JS by IndexReloaded some JavaScript will remain in HTML. Setting "Defer remaining JS after processing" defers this JavaScript. This is important for the (final) elimination of renderblocking elements.
You got a 'jquery not found'-errormessage after enabling "Defer remaining JS after processing"? Setting "Do not defer Jquery" allows to exclude jquery from deferal.
The "CSS compression" setting enables or disables minimizing CSS files. By reducing CSS size, the plugin significantly improves page load times.
The "Minify JS" setting enables or disables optimizing website performance by reducing JavaScript file sizes. This feature uses a third-party JavaScript minification tool, run on your site. By enabling Minify JS you reduce JavaScript size, optimize script delivery and boost web page efficiency.
Setting "Minify exclude list" allow to identify JS that must not be minified, if ever this would be needed.

#### Critical CSS

This feature of the IndexReloaded significantly improves page loading speed by focusing on critical aspects of optimization. Above-the-fold Critical CSS adds up to non-blocking JavaScripts to streamline the critical path for fast rendering of essential content. Setting "enable folding" will split the grouped CSS to inline critical CSS and to deferred (asynchronously lazy load) non-essential CSS for improved performance. In addition, if some CSS-part found in original HTML does not sort out any critical CSS, this CSS may be dropped with setting "Remove CSS from CSS below if it doesn't sort out CCSS".
Critical CSS is based on the HTML as it is displayed on the client around 2 seconds after page load. With this the urge to specify "Tags to keep above the fold" (or classes or Ids to keep above the fold) is very rare.

#### Preloading

This setting within IndexReloaded focuses on optimizing website load times by proactively preloading critical resources. This feature allows to force preloads of essential assets, such as fonts, scripts, or CSS files that are required for the initial page rendering process. By fetching these key requests ahead of time, it significantly improves page speed.
However as these settings apply to every page of the site use preloading prudently. Apart from font-preloads, a preload will only show up if the filename is present in the HTML of the page.

#### HTML Cleanup
The settings here allow to remove parts of the HTML which are often considered as useless. Note that setting "Remove HTML-comments and generator meta" leaves intact comments by ko.js.
But most HTML clean up is done automatically. For fonts loaded over Google it uses the "swap" mode for web fonts to ensure that a fallback font is displayed immediately, preventing a flash of invisible text (FOIT). Additionally, it optimizes the loading of Google Fonts, ensuring that content remains visible during the font-loading process. 
Also it makes sure that all images have alt, width and height attributes.

### Uninstallation

When you delete the plugin, it will automatically keep all settings. During this process, the directory containing optimized files will be removed and possible cache entries in the database will get deleted. Please note that uninstalling the plugin will not remove the settings of the plugin.

### Feedback, Bug Reports, and Logging Possible Issues

If you have any questions, suggestions, or encounter issues related to site speed optimization, we encourage you to contact us at contactindexreloaded@toctoc.ch. Whether you're a user, developer, or tester, your feedback is essential to improving our services.

To facilitate troubleshooting, IndexReloaded offers error logging capabilities. If you encounter any problems, you can help us in resolving them by providing us with the relevant error log file. Your assistance will help us improve your experience with IndexReloaded.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/indexreloaded` directory, or install the plugin directly from the WordPress plugins screen. We highly recommend creating a backup of your site beforehand, just as you would before installing any new plugin.
2. Activate the plugin through the "Plugins" screen in the WordPress dashboard.
3. Open the plugins settings page, check the excludes list and test if your frontend loads correctly (no display- or javascript-errors). If needed add the appropriate excludes to exclude list.
4. When done, enter your licence key and enable "Critical CSS" -> "Enable folding".


== Frequently Asked Questions ==

= Where can I get more information about IndexReloaded? =

Please see our website. (https://www.toctoc.ch/en/getindexloaded)

== Screenshots ==

1. **Overview**, the first tab displays the usage figures for files and, in the licensed version, also for the ObjectCache. Here you can tidy up if necessary.
2. 3rd tab IndexReloaded shows a first **recommendation for the Excludelist**, a list of identifiers, with which certain JavaScript and CSS files can be excluded from processing by IndexReloaded.
3. In **General Settings**, you control the storage location and storage time of the CSS and JavaScript files created by IndexReloaded. A license key can be stored here and the plugin can be completely deactivated or activated.
4. **CSS and JS Processing** control whether and how JavaScript and CSS are processed. Here is also the centrally important Exclude List, which is used to specify specific elements that cause JavaScript errors or display errors when processed by IndexReloaded.
5. More Options located in **CSS and JS Processing**. The minification of JavaScript and CSS can also be switched on or off here, also deferal (delayed loading) of JavaScript files contols here.
6. The options for **Critical CSS** are only available in the licensed version. But we provide a free licensing key on our website and, even if IndexReloaded is not licensed, the available performance gain is already 60 to 70% of the licensed version with Critical CSS.
7. The **Preloading** options apply to every page which contains the file to preload its page source. This does not apply for font-preloads, these are loaded in every page.
8. The **HTML cleanup** options can be used to remove other elements of the website, such as comments, certain meta tags, etc
9. Last but not least, a **Monitoring window** is available in the frontend, which gives you an insight into individual processing times, number of data involved, etc. when IndexReladed is executed in the frontend.
10. **Monitoring window** displaying in frontend.
11. What can be achieved with IndexReloaded on PageSpeed Insights? Without IndexReloaded, the countless links to CSS files and JS files lead to long waiting times on the client.
12. IndexReloaded eliminates render-blocking resources and reduces the number of calls to other files on the server. The result is clearly noticeable and measurable.

== Changelog ==

= 1.2.1 =
* Fix	- Possible error in indexreloaded-frontend.php, because WordPress-backend-function is_plugin_active() is not present, is fixed
* Fix   - Minor CSS improvement in backend (Show Settings button) 

= 1.2.0 =
* New	- Compatibility with Page Cache-Plugins WP Fastest Cache, W3 Total Cache, Super Page Cache, Rapid Cache, Hummingbird, WP Optimize, Hyper Cache, SpeedyCache, 
          Comet Cache, Cache Enabler, Borlabs Cache, Cachify, Swift Performance Lite, LiteSpeed Cache and WP Super Cache. 
          The compatibility concerns: CCSS to be delivered on 2nd page call, serving fresh pages after ajax-requests, serving fresh page after calling it using URL-paramenter "?forceNewFiles=1",
          for these reasons individual page cache files need to be removed. After file deletion in backend, the entire page cache is purged.
* Fix   - Host external CSS and JS locally: External font-files in CSS get hosted locally as well.
* Tweak - Keep time for generated CSS, JS and external files has new default value 0 (no automatic deletion after specified time in days).
* Fix	- Checking if file exists before unlinking in backend

= 1.1.1 =
* Fix   - "Deactivate on pages" accepts partitial page-URLs (URL must contain 3 or more slashes) 
* Fix   - Host external CSS and JS locally: External files from maps.gooleapi.com won't be saved locally, because they don't work
* Fix   - External CSS and JS, if added to the excludes list, will be ignored
* Fix   - CCSS: Selector extraction improved, support for another kind of bracket-expression, added enhanced support for more complicated where-not contstructs

= 1.1.0 =
* New   - Host external CSS and JS locally, it can be enabled or disabled (default) in Backend -> Settings -> Rules for CSS and JS processing.
		  See if any external files got locally hosted in Overview-panel under Files
* New   - Automatic preloads for generated css, js and jquery
* Tweak - List of recommended plugin-excludes reduced
* Tweak - Exclusion of Inline-JS happens automatically only if reference to a "nonce" is contained and detected, CDATA is not a filter no more
* Fix   - Processing now includes inline-scripts, found directly after calls to externally hosted scripts, properly
* Fix   - Processing for CCSS now includes inline-scripts with type="text/html" in models HTML-base, before only scripts with type="text/template" got included
* New   - When JS defering is active, then inline-JS transforms to defered JS as well. Inline-code will be base64-encoded and loaded as src="data:text/javascript;base64,... 
          Note that servers having the "Header set Content-Security-Policy"-directive set up in Apache-configuration need to add to "script-src" data for data, like "data: data;"
* New   - In backend a new button "reveal cached pages" has been added. It includes removal for individual cache-entries.
* New   - In settings we added a link back to overview in region of top of page.
* New   - Processing for CCSS retriggers model creation if size of html to be processed exceeds former size by more than 40 Bytes. 
          This addresses the "Empty cart CCSS"-issue, where CCSS created for the empty cart-page is not covering the needs of the cart-page with products.

= 1.0.2 =
* New   - CSS inside &lt;noscript&gt;-tags is not processed anymore
* New   - Objectcache is deleted for IndexReloaded after customizing the theme
* Tweak - Preloads of font-file will only load, if the referenced filename is present in pagesource, however this requires critical CSS to be set inline to the page
* Fix   - Support for external JS-files - without .js-fileextention - that are loaded using CGI-call to server
* Fix   - Critical fonts processing in CCSS-creation, missing font-weight 700, when only implied by strong, h1 or h2-tags, is fixed
* Fix	- Selector :checked not in CCSS, when critical

= 1.0.1 =
* Fix	- Possible error in indexreloaded.php, because WordPress-backend-function is_plugin_active() is not present, is fixed
* Fix   - Added difference in classes (model from server versus model from client) to valid client-model 
* New   - Added critical fonts processing in CCSS-creation
* Tweak - Added JS-file parameters to grouped extracted JS as variables (with value)
* Tweak - Preloads of images, CSS- or JS-file will only load, if the referenced filename is present in pagesource
* Fix   - CCSS processing of selectors having multiple tilde (~), plus (1) oder '>'s now translate to check selector sequence with correct element indexes

= 1.0.0 =
* Initial release.