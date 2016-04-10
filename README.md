roddick
=======

Start the built-in php web server as a background process.

Install this package with composer: `composer require smrtr/roddick:~1.0`.

Access the cli at `vendor/bin/roddick`.

## Start server

    $ roddick start

Starts the server in a background process.

By default the server will listen on http://127.0.0.1:8080 and use the current working directory as the document root.

    Usage:
      start [options] [--] [<address>]
    
    Arguments:
      address                <host>:<port> [default: "127.0.0.1"]
    
    Options:
      -p, --port=PORT        Override the port number of the given address
      -d, --docroot=DOCROOT  Document root for the web server
      -r, --router=ROUTER    Custom router script

You may provide the port as part of the address, or using the port option. If you provide both then the port option
will override any port defined in the address.

## Stop server

    $ roddick stop

Stops the web server process that was started with `roddick start`.

    Usage:
      stop [options] [--] [<address>]
    
    Arguments:
      address               <host>:<port> [default: "127.0.0.1"]
    
    Options:
      -p, --port=PORT       Override the port number of the given address

The address and port are handled as in the start command.

## Check server

    $ roddick status

Checks if a web server is already running.

    Usage:
      status [options] [--] [<address>]
    
    Arguments:
      address               <host>:<port> [default: "127.0.0.1"]
    
    Options:
      -p, --port=PORT       Override the port number of the given address

The address and port are handled as in the start command.

---

### Acknowledgements

Symfony [did this first](https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Command/ServerStartCommand.php),
but I needed to decouple it from the symfony framework for use in my own testing environments.
