#DEPRECATION NOTICE

ExoWP has been deprecated in favor of [WPLib](http://github.com/wplib/wplib/).

#ExoWP
An MVC Exoskeleton for WordPress

> _"Exo doesn't change WordPress, it just makes it stronger."_

**NOTE: Exo is in Alpha and thus anything written below is subject to change prior to our 1.0 release.**

##Overview
###Exo's Purpose
Exo is an [MVC Foundation Layer](http://en.wikipedia.org/wiki/Model–view–controller) for WordPress to empower programmers to create complex web apps using WordPress without prematurely reaching the point of _"too much complexity for WordPress to handle."_

With Exo, more programmers can say that _"WordPress is the right tool for the job"_ than they could otherwise say without Exo.

###Pronunciation
Pronounced _"ex oh"_ where the _"WP"_ is silent. The WP is just there to help non-WordPressers to know it is for WordPressers and not for them.

###Meaning
Exo is short for _"Exoskeleton for WordPress"_. Which you could have figured out from our tagline, if you had been paying attention. :)

### Exo's Target Audience 
Exo is explicitly _**NOT**_ for end-users.

Exo is instead explicitly architected to be used by WordPress professionals; programmers, site-builders, and those designers who can copy-paste PHP snippets. And Exo can be extended by professional PHP programers and/or teams having professional programmers willing to learn and use PHP. 

#### However…
And when used by programmers to create themes or [application plugins](http://codex.wordpress.org/Must_Use_Plugins), **Exo can provide great benefit to end-users**. Exo is just not a solution for non-programmers to add to their WordPress websites on their own.

### Exo Does Not Change WordPress
Unlike other attempts to transform WordPress into an MVC framework, Exo was built with a goal of augmenting WordPress, not one of changing WordPress. 

Most other WordPress MVC libraries we've have been attempts to transform WordPress' fundamental nature to that of an MVC framework similar to other MVC frameworks targeting programmers. **But therein lies madness.** 

WordPress transformed _makes WordPress fundamentally not WordPress_. Transforming WordPress to MVC will never work compatibly with the larger WordPress ecosystem of plugins and themes; those attempts have unfortunately created fringe solutions, at best.

Instead of transforming WordPress, **Exo is pure-WordPress** at its core, Exo **simply adds structure** around WordPress that allows a developer to build more robust applications faster than he or she could using defacto-standard WordPress plugin and theme development techniques.

####Exo is WordPress-ish
Using a term we first heard coined by [Taras Mankovski](http://www.linkedin.com/in/tarasm), Exo is _WordPress-ish._  Whenever possible, WordPress attempts to behave like WordPress by augmenting it, not transforming it.  

WordPress developers, designers and site builders should be able to use as little or as much of Exo as they need and/or feel comfortable with, and they should never have to revert to using techniques they are not yet comfortable with, unless of course they are pushing themselves to grow their development skills.

 
###Exo's Controller
The _"C"_ in the MVC architecture refers to [a Controller component](http://stackoverflow.com/questions/1015813/what-goes-into-the-controller-in-mvc) and in Exo **the controller is WordPress itself**. 

WordPress already routes URLs, handles user permissions, loads _"views" (a.k.a. "template files" in non Exo-augmented WordPress vernacular)_, and WordPress coordinates generating the HTML for each HTTP response given each HTTP request. 

So there's no reason Exo needed to implement it's own controller; WordPress already handles that part just fine. 

####Exo is Model-View-WordPress
Exo instead handles the [models, views](http://en.wikipedia.org/wiki/Model–view–controller), [collections](http://en.wikipedia.org/wiki/Collection_(abstract_data_type) and [mixins](http://en.wikipedia.org/wiki/Mixin). Because of this, we like to think of Exo as a variant of MVC, a varient we like to call **MVWP**, an initialism for _"Model-View-WordPress."_

####Exo-enhanced URL Routing
Exo also augments [WordPress' URL routing](http://ruslanbes.com/devblog/2013/04/03/wordpress-routing-explained/) when WordPress' routing is not sufficient for the complexity of the URLs that can be required in a web app vs. a blog or brochure website. But even when augmenting WordPress' URL routing Exo still respects WordPress' approach to routing, e.g Exo-enhanced routing simply sets `$wp->query_vars` as WordPress would do itself and then allows WordPress to continue its role as controller.

### Exo is Object-Oriented, Of Course
Exo is [highly object-oriented](http://tommcfarlin.com/object-oriented-wordpress-plugin/), as would be expected of a solid MVC framework. And by object-oriented, we don't mean [just using classes as namespaces](http://wp.tutsplus.com/tutorials/plugins/two-ways-to-develop-wordpress-plugins-object-oriented-progamming/), we mean using and leveraging the benefits of object orientation in all it's glory.

But don't worry, **you don't have to learn Exo's OOP all at once** _(or even at all if you only want to create themes.)_ Just use as much as you are comfortable with and grow your skills over time.

##Exo's Benefits
The benefits to learning and using Exo when building WordPress web apps are:
- **Handle Greater Complexity** - Exo allows an application's complexity to grow to a significantly greater level than can be realistically managed using defacto-standard approaches to building WordPress sites. 

- **Reduces Duplication in Themes** - Most WordPress themes developed for highly custom sites are littered with complex business logic that is often duplicated in many places. Intermediate use of Exo can reduce the complexity, and advanced use can all but eliminate it. _(For you new-to-WordPress MVC aficionados we are talking about Separation of Concerns here.)_

- **Consistency** - WordPress is notoriously inconsistent in [its APIs](http://codex.wordpress.org/WordPress_APIs) and a fundamental principle of Exo's API is rigid consistency, while being as WordPress-ish as possible.

- **Reusability** - WordPress by it's very nature makes it possible to create reusable end-user components, known as plugins and themes, but WordPress' structure does not encourage development of components that are easily reusable by developers.  Exo, by it's very nature, changes that. Development teams should be able to easily build reusable components they can leverage across many different projects. Further, if a lot of WordPress development teams choose to standardize on Exo then expect many reusable classes to emerge on Github.com.

- **Testability** - Professional developers employ automated unit and regression testing to ensure the changes they make while evolving their solutions don't break existing functionality. Yet WordPress is notoriously difficult to test. Not so with Exo; Exo's object-oriented nature makes WordPress sites  much easier to test.

##Exo's Features
The features available to those who learn to use Exo when building WordPress web apps are:
- **Models, Views and Collections**
- **Shared Behaviors using Mixins** 
- **Scoped Hooks**
- **Application Classes and Delegated Helpers**
- **Autoloading**
- **Github-centric**
- **Context-sensitive URL Routing**
- **Uses WordPress Theme Templates**
- **Flexible Template Parts**
- **Repository of Standard Classes** _(future)_
- **For Agency-built Web Sites/Apps** 


###Exo's Models, Views and Collections
Exo offers MVC model, view and collection classes **for standard post types, standard taxonomies, users and many of the standard options**. Exo uses these models, views and collections to encapsulate most of the effort required to interact with them and in a consistent manner. 

    $model = new Exo_Post( $post );
    echo $model->get_title(); 						// Same as "the_title();" 
    echo $model->title;       						// also same as "the_title();"  
    echo $model->get_field_value( 'post_title' );   // Same as "echo $post->post_title;"
    echo $model->get_post()->post_title;   			// Same as "echo $post->post_title;"
    echo $post->post_title;   						// *IS* "echo $post->post_title;" :)
    
	$view = new Exo_Post_View( $model );
	$view->the_permalink();							// Outputs <a> for the Post's title
	echo $view->get_permalink_html();				// Same as "$view->the_permalink();"
	
	$views = new Exo_Post_View_Collection();		// Defaults to 10 most recent post views
	$views->each( function( $view ) {				// For each of the post views 
	  $view->the_template( 'badge' );               // Display the 'post-badge.php' template part
	});
	
    
More ambitious Exo developers can develop models, views and collections for their custom post types, custom taxonomies and custom options based on the base classes found in Exo. 

In the future Exo will certainly offer base model, base view and base collection classes for other needs, such as for custom tables as well as for Sites and Networks in Multisite.

### Exo's Mixins to Manage Shared Behaviors 
Probably **the most beneficial feature of Exo** is its support for [Mixins](http://en.wikipedia.org/wiki/Mixin) to manage shared behavior between multiple models, multiple views and/or multiple collections. 

For example, assume we have a mixin named _"Likable"_ that provides functionality for liking posts. Your WordPress app might have post types for news articles, case-studies, products, and more and you want all of them to have that behavior, but you don't want your team-member post types to have the like buttons displayed.

Using Mixins is a highly powerful way to avoid to spaghetti coding with copious `if` statements containing embedded business rules littered throughout and duplicated in themes and plugins. With mixins you can simply add _(something like)_ the following code to your themes:

    <?php if ( $view->has_behavior( 'likable' ) ): ?>
      $view->the_template_part( 'likable-button' );
    <?php endif; ?>  
      
And in many cases Exo will do the work for you, such as adding metaboxes in the admin to the post types that need them.

 
### Exo's Scoped Hooks
In WordPress, all action and filter hooks are global. With Exo action and filter hooks are extended and can also be scoped to either classes or objects _(objects == instances of a class.)_

Exo uses WordPress' hook system for these scoped hooks. For example this is **effectively how** Exo implements an instance action and a static _(class)_ filter, respectively:

    // Assumes an instance hook called initialize().
    $instance_hash = spl_object_hash( $this );
    $this->add_instance_action( "{instance_hash}->initialize()", array( $this, 'initialize' ) );

    // Assumes a class hook called initialize().
    $class_name =  = get_called_class();
    self::add_static_filter( "{$class_name}::initialize()", array( $class_name, 'initialize' ) );

Many of the bugs we've seen creep into our WordPress implementations have been from globally scoped action or filter hooks that have executed when we didn't expect them to. By scoping to classes and/or instances developers can greatly reduce the number of bugs they have to work through when building a complex site.

### Exo's Application Classes and Delegated Helpers
Exo provides several classes designed to support the creation of an main class for your web app, and Exo uses them itself. These classes are:
- `Exo_Singleton_Base` - Parent class for `Exo`, this class offers a public `$skeleton` property that is designed to contain an instance of `Exo_Delegating_Base` and this class routes any static method calls to the instance methods of the object contained in Exo::$skeleton, i.e. `Exo::register_helper( $helper_class )`  ultimately calls `Exo::$skeleton->register_helper( $helper_class )`.
- `Exo_Delegating_Base` - Parent class for `_Exo_Skeleton`, the class for the object assigned to `Exo::$skeleton.` This class provides registration for helper classes that extend it's partner class, i.e. in Exo that class is `Exo`. For example, there is an `_Exo_Mixin_Helpers` class  that contains a `static function register_mixin()`. By calling `Exo::register_helper( '_Exo_Mixin_Helpers' );` `_Exo_Skeleton`will delegate `Exo::register_mixin( 'My_Sociable_Mixin', 'sociable' );` to be handled by the `_Exo_Mixin_Helpers` class.
- `Exo_Helpers_Base` - Parent class for a class designed to be used as a helper for a `Exo_Singleton_Base` class. Using `Exo_Helpers_Base` as a Parent class for your helper classes is recommended but not required, especially when the helper is just one or more methods and not the entire class.

While it may sound overwhelming at first, it is only complicated for the person developing the Application class, and once learned it is quite manageable. 

But for those _using_ the application class, such as `Exo` itself or your own application class, the benefits of this approach are to greatly simplify the API that a themer would need to learn in order to program the application's themes and/or plugins for the application.

Using this approach the application plugins/library can contain a large number of classes but the user of the plugin/library only needs to remove the one Application class; `Exo` in our case but you can use it to do the same for your classes, and even have your class name be able to invoke `Exo` methods.

For example, the following is how a themer might insert a post:

	Exo::insert_posts( 'post_title=My+First+Post' );
	
When in fact the `insert_posts()` method is implemented in the `Exo_Post_Base` class and without this delegation the user would need to know to use this instead:

	Exo_Post_Base::insert_posts( 'post_title=My+First+Post' );
	
Requiring users to know all the classes in your application and what methods they implement might be okay you only have a few classes, but if you have tens or hundreds of classes it can overwhelm a potential user. And they are likely to be overwhelmed enough that they would sour on using your solution and instead choose a solution that is easier to comprehend, even if that easier solution doesn't meet their needs nearly as well as yours.

### Exo's Autoloader
Unlike WordPress, which does not have many classes nor is designed to be programmed in an object-oriented manner, Exo has many classes and thus benefits from an autoloader. 

Further, any themes or application plugins developed using Exo can also easily leverage the Exo autoloader simply by registering the directory(s) in which it's classes are located and the prefix you use for your classes:

	// Registers the subdirectory /classes to contain class files.
    Exo::register_autoload_dir( __DIR__ . '/classes', 'YourApp_' );

   	// Registers the subdirectories /models/{$model_type} to each contain class files.
    Exo::register_autoload_subdir( __DIR__ . '/models', 'YourApp_' );

#### Class Filenames
Exo's Autoloader assumes that the class filenames will have the same name as the classes except the class prefix and optional underscore stripped, underscores converted to lowercase, and with or w/o a filename prefix of `'class-'`. For example:

- `YourApp_Product` => `class-product.php` or just `product.php`.
- `_YourApp_Product_Helpers` => `class-product-helpers.php` or just `product-helpers.php`.
    
### Exo is Github-centric
Unlike WordPress itself which is still mired in legacy Subversion and SVN's lack of solid support for [social coding](http://whatis.techtarget.com/definition/social-coding), Exo is first and foremost a GitHub project and that means it is much more open for collaboration. 

Feature discussions are welcome on the tracker and pull requests are encouraged.

### Exo's Context-sensitive URL Routing
WordPress has _"context-free"_ URL routing meaning that you do not have to understand the context of the content in the system or the order of the routing rules to know what a WordPress pretty URL will load. For example, assuming you have a _"Products"_ post type you can be pretty sure that the following URL will load the product named ACME Widgets:

	http://example.com/products/acme-widgets/
	
Further, assuming you understand WordPress detail routing rules you know that this will load a `post_type=='page'` with the `post-name=='about'`:

	http://example.com/about/
	
However, what if you would like to have URLs where your product URLs omitted the `'/products/` segment and instead used the first segment for the product name slug, like this:
	
	http://example.com/acme-widgets/
	
In general you can't do this with WordPress because if you configured WordPress to load products using the first URL path segment then WordPress would not be able to load pages for posts of `post_type=='page'`; it would be ambiguous. But we can do it using Exo, and that's what we mean by _"context-sensitive"_ vs. _"context-free"_.

Here's all you need to do to route products in the manner using Exo where the following method would be included within an `'exo_url_route'` hook where you first defined the var and it's properties and then define the route:

    Exo::register_url_var( '{product}', 'post_type=product' );
    Exo::register_url_route( '{product}' );

Or more simply there you just define the properties with the route:

    Exo::register_url_route( '{product}', 'post_type=product' );

Or more simply there where Exo infers the post type:

    Exo::register_url_route( '{product}' );

### Uses WordPress Theme Templates
This is not so much as feature of Exo so much as it is a lack of limitation when using Exo. When using Exo you can continue to theme WordPress exactly as you have before, without limitation. 

However, if you want to enforce limitations to improve the robustness of your application, such as the removal of all global variables from visibility in your theme templates, you can do so by setting an Exo config option either in your `'wp-config.php'` file:

    define( 'EXO_THEME_GLOBALS', false );
    
Or inside your application class' optional `on_load()` method:

    Exo::$THEME_GLOBALS = false;

### Exo's Flexible Template Parts
WordPress' introduction of the `get_template_parts()` function was a significant improvement over prior versions of WordPress, but since it was released the projects WordPress has been used for have cause `get_template_parts()` to show it's age. Exo solves this with limitation by introducing a method of the Exo_View_Base class named `the_template_part()`.

Minimally `the_template_part()` uses the `the_` prefix to indicate it is `echo`ing HTML to the requestor's browser instead of returning a value within the program and the name `get_template_parts()` might otherwise imply.

More significantly `the_template_part()` supports a infinite typed matrix of template parts rather than a simple two (2) level hierarchy of template part options. Specific each view has a template part _"type"_ and that type determines the directory structure. So for our Product view the type is likely to be `'products'` and thus the following path illustrates where we might find the desired template: 

    {$theme_dir}/template-parts/products/product.php

However if we passed `'reviews'` to `the_template_part()` like so:

    $view->`the_template_part( 'reviews' )`
    
The template file it is like to load might be:

    {$theme_dir}/template-parts/products/product[reviews].php
    
But that's just the tip of the iceberg as Exo's Template Parts have an order of magnitude more power that the above example illustrates.

### Repository of Standard Exo Classes
One of the more significant failings of WordPress governance has been its disinterest in overseeing the cultivation of _(defacto-)_standards extensions for WordPress. 

While they have done a phenomenal job of managing WordPress core they have done very little _(nothing?)_ to catalyze the emergence of defacto-standard solutions for WordPress upon which other developers would then be more likely to build upon. 

For example, there have been numerous Event plugins but no leadership from WordPress to mold a shared solution that would meet the needs of the 80% of users who need Events functionality for their website. Similarly there have been numerous forms plugins but again no attempt to bring the various developers together to collaborate on a shared standard. 

This lack of initiative results in constant reinvention of similar functionality and situations where users can have _"X"_ or they can have "Y" but you can't have both _"X"_ and _"Y"_ _(just research the [feature sets across membership plugins](http://chrislema.com/choosing-wordpress-membership-plugin/) if you want to see those concerns playout to the limit.)_ We see this as a hugely missed opportunity, and we plan for Exo to be the catalyst for change related to the need for defacto-standard functionality, at lease among Exo users.

### Exo is for Agency-built Web Sites/Apps
Most implementations of WordPress are either blogs &ndash; a use-case WordPress nails completely without custom development &ndash; or websites for small businesses where the budget required to develop the site is a primary factor. For either of those use-cases off-the-shelf themes and plugins can do an incredible job of providing really high value compared with the cost.

However when you building a website whose budgets is between US$100,000 and US$1 million or more, off-the-shelf themes and plugins can become a living nightmare. For those web projects and the agencies who implement them having a dependency of a plugin or theme developed with end-users in mind, without a solid and testing developer API for extending them, without having been required to withstand a professional security review, and without a clear and certain approach for providing bug-fixes back to the developer and having them committed the smart choice is to build all custom functionality beyond core WordPress from scratch.

And this is where Exo shines. Exo was developed **with the needs of agencies who are implementing large and expensive WordPress-based web projects for their clients.**


## Exo's Conventions

Exo follows WordPress coding conventions as closely as possible but also adds a variety of additional conventions to help manage complexity and maintain consistency.

- **Leading underscores** - Class, methods, and variables whose names begin with a leading underscore, such as `_Exo_Skeleton`, are designed to be internal to Exo and not used by code outside of Exo. In some cases they are private or protected but in other cases the may be public simply because the Exo architecture would not work correctly if they were private or protected.

- **View and Collection Delegation** - Exo automatically delegates instance method calls and instance property accesses to views and collections down to their models. For example if `$view` was an instance of `YourApp_Post_View`, and  `YourApp_Post` was the model for `$view` then `echo $view->get_title();` would automatically delegate to `$view->model->get_title()` assuming `YourApp_Post_View` did not define a method named `get_title()`. 

- **Mixin Owner Delegation** - Exo also automatically delegates instance method calls and instance property accesses to mixin class instances, or back to the owner if a mixin. For example if `YourApp_Post` contained a mixin named `YourApp_Skinnable` with a method `get_skin()` then `$view->get_skin()` would automatically delegate to `$view->model->get_mixin('skinnable')->get_skin()`.

- **View Method Prefixed with "the_"** - Exo automatically invokes the method prefixed with 'get_' any time a same-named _(virtual)_ method prefixed with `the_` is called on a View. In this case the value returned by the `get_` method will be `echo`ed.  Thus when the variable `$view` is of class `Exo_Post_View` and it contains a `get_title()` method then `$view->the_title();` will delegate to the view's model and it will effectively `echo` the return value of `$view->model->get_title();`

- **Virtual Properties** - On any instance of `Exo_Instance_Base` Exo automatically invokes the method prefixed with `get_` any time a property is accessed with the same name sans the `get_` prefix. For example, when the variable `$view` is of class `Exo_Post_View` and it contains a `get_title()` method then `echo $view->title;` will delegate to the view's model and it will effectively `echo` the return value of `$view->model->get_title();`

##Exo-Deprecated WordPress Techniques
Exo deprecates some of the following coding and site building techniques. And by deprecate with mean that **you can still use them**, if you are more comfortable with them, but _Exo provides much better alternatives_ if you are willing to learn and embrace them:

- **The Loop and its Global Variables**
- **`get_template_part()`, `get_header()` and `get_footer()`** 

### The Loop and its Global Variables
Using Exo Views and Collection Views is a much better approach to theme development than the using WordPress Loop and it's associated Global Variables. With Exo you can write code that is much less likely to be broken by future modifications to your source code.

### get\_template\_part(), get\_header() and get\_footer()
`get_template_part()`, `get_header()` and `get_footer()` are not bad per-se, but WordPress code does not provide the hooks required to gain the benefits found in Exo. 

Instead Exo offers the following methods of the base View class:

- `$view->the_template_part()`
- `$view->the_mixin_template_part()`
- `$view->the_header()`
- `$view->the_footer()`

These methods provide a highly flexible system for managing template parts within the `/template-parts/` subdirectory of the theme directory.

## Exo's Origin
Exo wasn't envisioned as an academic, _"Wouldn't it be cool if?"_ project to create an MVC layer for WordPress. We built _(the precursor to)_ Exo because we didn't really have a choice. Without it, our project would have failed.

### Architected Because We Had To
Exo's fundamental architecture principles were developed in response to the challenge of building a complex intranet application where the project sponsor chose WordPress even though most people whould have said WordPress was not a fit. Without the precursor to Exo, we would have never been able to deliver the application with the features the client was expecting without major bugs. With Exo's precursor, we succeeded brilliantly. 

And for posterity, the project sponsor was the global marketing team of a Fortune 100 beverage maker located in the southeastern United States.

### Who Architected Exo?
The [lead WordPress architect](http://wordpress.stackexchange.com/users/89/mikeschinkel?tab=answers&sort=votes) on the project _(and the lead developer of Exo)_ was/is a programmer with 20+ years of database development experience, and though he had been aware of MVC frameworks for years instead chose to specialize in extending WordPress while staying true to the fundamental nature of WordPress. His two (2) most recent previous WordPress architecture projects included Great Jake's Rainmaker platform for large law firm websites &ndash; which was advanced WordPress but not MVC &ndash; as well as the Best Practices Assessment App for the Australian Banana Grower's Council &ndash; which used models but not views.

### Exo's Non-Deviation Principle
Thus enhancements to WordPress had to be WordPress-ish for both the project that birthed the principles on which Exo are based, and for Exo itself. Exo does not force deviation from WordPress' fundamental nature but where deviation is offered because of the benefits of deviation, deviation is not be required _(with a few caveats; see the "Deprecation" section below.)_

### Inspired by Backbone.js
Early during the project that birthed the architectural principles for Exo, the lead developer tackled learning [Backbone.js](http://backbonejs.org) to add some more advanced admin functionality to the project _(truth be told, it was [WPScholar](https://bitbucket.org/wpscholar/) that completed the Backbone.js work.)_ 

It was while studying Backbone.js it became apparent that an MVC library need not have a [Ruby on Rails' style Controller](http://guides.rubyonrails.org/action_controller_overview.html) to be incredibly useful and that views would solve the future problems envisioned for the best practices assessment app mentioned above. And it was with that epiphany that Exo was first conceptualized.

### Bumps and Bruises Begone
Of course the original project was the first generation implementation of the Exo architectural principles and it was developed on a deadline with more than one cook concurrently in the kitchen without a code-review-before-commit process. Thus we both made some architecture errors, we committed some less-than-elegant code that stuck, and we over-engineered it in places that if hindsight could have informed us back then it would have told us _"You're Not Going to Need It."_

So Exo is a re-imagining of the core architecture of that project without all the cruft. Unlike that project, all code that gets committed to Exo is committed with conscious consideration of how it fits into the overall architecture and if we really need it.  Here's hoping we have and will continue to make the right choices in that regard.

##Exo's License
Exo is licensed using GPLv2 to be compatible with WordPress itself.


