Statemachine-Example
====================

Implementation of the [metabor/statemachine](https://github.com/Metabor/MetaborStd)

What's inside?
---------------

The example from my talk is in the namespace Example and the slides (in german) in the docs directory.
In the namespace MetaborStd are abstract types defined.
These are exemplified implemented in the namespace Metabor.

1) Installing
-------------

### Use Composer (*recommended*)

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

```bash
$ curl -s http://getcomposer.org/installer | php`
```

Then, use the `create-project` command to generate the statemachine-example project:

```bash
$ php composer.phar create-project metabor/statemachine-example path/to/install`
```

Composer will install it and all its dependencies under the
`path/to/install` directory.

### Download an Archive File

You can also [download an archive](https://github.com/Metabor/Statemachine-Example/archive/master.zip) and unpack it somewhere under your web server root directory.

You also need to install all the necessary dependencies. Download composer (see above) and run the
following command:

```bash
$ php composer.phar install`
```

2) Run the example project
--------------------------

As a starting point I recommend the index.php

You can run it either from the command line:

```bash
$ php index.php`
```
    
Or by using a webserver. If you run PHP 5.4 or higher you can use the builtin webserver:

```bash
$ php -S localhost:8000 -t path/to/install`
```

You can now access it in the webbrowser with [http://localhost:8000/index.php](http://localhost:8000/index.php).

To display the process a a graph, you'll have to
install GraphViz (`dot` executable). Users of Debian/Ubuntu-based distributions may simply
invoke `sudo apt-get install graphviz`, Windows users have to
[download GraphViZ for Windows](http://www.graphviz.org/Download_windows.php) and remaining
users should install from [GraphViz homepage](http://www.graphviz.org/Download.php).

You can now access the two processes with the get parameter "process":
[http://localhost:8000/graph.php?process=prepayment](http://localhost:8000/graph.php?process=prepayment)
[http://localhost:8000/graph.php?process=postpayment](http://localhost:8000/graph.php?process=postpayment)
in the webbrowser.

Have fun and play with the example by changing it ;)

All Metabor libraries are released under the MIT license.

Enjoy!