	# Shani Web Application Framework

**Shani** is an open source web framework designed to enable fast application
development with minimal efforts while performance, security, creativity
and modern web application development practices coexists.

## Use Cases

Use **Shani** to build cilent-side or server-side application. You can also use
your favorite front-end framework while **Shani** stands on back-end, or vice versa.

## Main Features

1. Scalability
2. Robust (Error torelancy)
3. Secure (CSRF, Session Mgt, Authentication & Authorization)
4. Fast
5. Multiprosess
6. Multithreaded
7. Event driven
8. Non-blocking
9. HMVC
10. Zero dependency
11. Dynamic Routing
12. Built-in web server
13. Web socket supports
14. State Management
15. Third-party library support
16. Best practices API supports
17. Supports JSON, XML, YAML, CSV and HTML
18. Load balancing (Round robin, preemptive, fixed)
19. Memory Management
20. Asynchronous

## System requirements

**Shani** runs well on Linux and Mac OS, However for Windows users they can use
Windows Subsystem  for Linux (WSL).

## Installation

No installation is required.
## Usage

To start **Shani** application web server, run the following command on terminal:

```bash
$ php index.php
```

### 1.0 Project Structure

Shani Application has the following project structure

	/root
		apps/ (Contains user applications. Create your applications here)
		config/ (Contains important server and hosts configurations)
			hosts/ (Contains host configurations files (hostname.yml). Register your application here)
				localhost.yml (can be customized)
			ssl/ (Contains server ssl certificate files for)
			mime.yml
			server.yml (Server configuration are written here, and can be customized.)
		gui/ (Contains support for GUI building)
			assets/ (Contains static files e.g: .css, .js,fonts etc shared by all applications)
			html/ (Contains html templates comes with framework)
		library/ (Contains files comes with framework that can be used directly by user application)
		shani/ (Contains core framework files)
		index.php (The entry point of a Shani application)

#### 1.0.1 User Application Structure

All folders below can be renamed. A typical user application folder structure may appear as the following:

	apps/
		demo/
            modules/
                module1_name/ (Can be any desired module name)
                    data (Data layer)
                        dto (Data transfer object)
                        models (Application models)
                    logic (Business logic layer)
                        services
                        controllers/
                            get/ (This is the request method as directory)
                                Resource.php (Can be any resource file)
                    presentation (Presentation layer)
                        views/
                            resource/ (All lowercase, must match resource file name)
                        lang/
                            resource/ (All lowercase, must match resource file name)
                        breadcrumb/
                            resource/ (All lowercase, must match resource file name)
                                functions/
                                    function-name.php (Must match function name in resource file class)
                                resource.php (must match module name)
                            module1_name.php (must match module name)

Let's assume we want to create an application called `demo`, our application has
one module called `school` and one resource file called `Hello.php`.

Now, look at the following example of a resource file:

```php
<?php

namespace apps\demo\modules\schools\logic\controllers\get {

	use shani\http\App;

    final class Hello
    {
        private readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        /**
         * Display school from Shani.
         */
        public function world()
        {
	        //sending output to user agent using default view file (world.php)
            $this->app->render(null);
        }
    }
}
```

Creating a view file:
(`apps/demo/modules/schools/presentation/views/hello/world.php`)

```php
<h1>Hello From Shani</h1>
```

Considering our example above, our application folder structure will be like this:

    apps/
        demo/
            modules/
                schools/
                    data
                        dto
                        models
                    logic/
                        controllers/
                            get/
                                Hello.php
                    presentation
                        views/
                            hello/
                                world.php

#### 1.0.2 Registering Application

The next step is to register our application so that it can be available on the web. You can do this by going to `/config/hosts/` and create a configuration file called `localhost.yml`. You can choose any name.

**Remember!**
*Each application MUST have it's own configuration file*

The following is the default application configuration that comes with **Shani**

```yml
# Environment variables are customs. Create enviroment variables and give them
# names of your choice, e.g DEV, TEST, PROD
ENVIRONMENTS:
  # Must extends shani\advisors\Configuration
  DEV: \apps\demo\config\Settings
  # Active environment can be any one of the provided above.
ACTIVE_ENVIRONMENT: DEV
# Whether an application is able to run or not
RUNNING: true
```

Let us customize this file to fit our application needs:

```yml
ENVIRONMENTS:
  DEV: \apps\demo\config\DevSettings
  TEST: \apps\demo\config\TestSettings
  PROD: \apps\demo\config\ProdSettings
ACTIVE_ENVIRONMENT: DEV
RUNNING: true
```

The next step is to create these configuration class files. We will create them under `apps/demo/config/`. Some content can be like so:

```php
<php

namespace apps\demo\config {

    use shani\advisors\Configuration;
    use shani\http\App;

    final class DevSettings extends Configuration
    {
        public function __construct(App &$app, array &$configurations)
        {
            parent::__construct($app, $configurations);
        }
        //Add all unimplemented methods here
	}
}
```

#### 1.0.2.1 Aliases

Let us assume our application is available through `http://localhost:8008`, we also want our application  to be available via `http://127.0.0.1:8008`, and `http://example.local`, or more...

There comes the concept of aliases, it is when the application is available via more than one host name. Just like we created `localhost.yml` file, we're going to create `127.0.0.1.alias` file and `example.local.alias` file.

***Remember***
_The file `localhost.yml` must be available before creating `.alias` file, otherwise your server will crush_

`127.0.0.1.alias` file contents

```alias
localhost
```


`example.local.alias` file contents

```alias
localhost
```

As we have seen, all `.alias` files must contains a host name they point to. This is how you create alias(es) in **Shani** application.
#### 1.0.3 Running The Application

Again, let's assume our application is available via `localhost:8008`. The default ports for our web server is `8008` for HTTP and port `44380` for HTTPS. We can use the following URL to call our function `world`.

```bash
$ curl http://localhost:8008/schools/0/hello/0/world
```

Congratulation! You have completed the first step to become a *Shani developer*.

### 2.0 URL structure

**Shani** application follows the following URL pattern

```
http://hostname[:port]/module-name/param1/resource-name/param2/function-name[/more-params][?query=q1]
```

Breaking down the pattern above, we can see that
* `module-name` represent the current resource module requested by user. This is a directory where all sub-resources under this module resides.
* `param1` and `param2` are the resource identifiers, can be a number, string or anything
* `resource-name` or sometime known as `controller-name` is a sub-resource. It is the actual implementation of the application, and
* `function-name` is a function name available in resource class definition. After function name, you can add more parameters or appending a query string

*Example:*

```
http://localhost:8008/products/201/sales/10/details
```

**Shani** application uses kebab to camel case conversion to convert URL into valid names.

1. `module-name` in URL represents a `module-name` directory in project structure.
2. `resource-name` in URL represents a `ResourceName` class in `module-name` directory, and `resource-name` directory in a view directory
3. `function-name` in URL represents a `functionName` in a `ResourceName` class file, and a `function-name.php` view in a view directory

Other URL parts remain unchanged.
## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.
## License

[GPL-3.0](https://choosealicense.com/licenses/gpl-3.0/)