# Silex React Renderer Service Provider 

# ReactRenderer

This provider integrates [ReactRenderer](https://github.com/Limenius/ReactRenderer) in your Silex project. This lets you implement your frontend with React.js and let php do the server-side rendering, allowing the development of universal (isomorphic) applications.

See https://github.com/Limenius/ReactRenderer for full documentation on the renderer. 

# Usage 

Basic example

```php
$app = new Silex\Application();
$app->register(new Teameh\Silex\Services\React\ReactRendererServiceProvider(), [
    'react.serverside_rendering' => [
        // using phpexecjs: 
        'server_bundle_path' => __DIR__ . '/path/to/your/javascript/development/bundle.js'
        // or using and external render server:
        // 'socket_server_path' => 'unix://node.sock'
    ]
]);
```

All config options

```php
$app = new Silex\Application();

$app->register(new Teameh\Silex\Services\React\ReactRendererServiceProvider(), [
    'react.default_rendering' => $app['debug'] ? 'client_side' : 'both',
    'react.serverside_rendering' => [
        'fail_loud' => $app['debug'],
        'trace' => $app['debug'],
        'mode' => 'phpexecjs',
        // using phpexecjs:
        'server_bundle_path' => __DIR__ . '/path/to/your/javascript/development/bundle.js',
        // or using and external render server:
        // 'socket_server_path' => 'unix://node.sock',
        'logger' => $app['monolog'],
    ]
]);
```

Configuration options: 

```
react.default_rendering:      string, either 'client_side', 'server_side' or 'both'

react.serverside_rendering:
    fail_loud                   bool, defaults to $app['debug']
                                  In case of error in server-side rendering, throw exception

    trace                       bool, defaults to $app['debug']
                                  Replay every console.log message produced during server-side rendering in the
                                  JavaScript console. Note that if enabled it will throw a (harmless) React warning

    mode                        string
                                  Mode can be `"phpexecjs"` (to execute Js from PHP using PhpExecJs),
                                  or `"external"` (to rely on an external node.js server) Default is `"phpexecjs"`

    string server_bundle_path   string (Only used with mode `phpexecjs`)
                                  Location of the server bundle, that contains the concatenated javascript bundle.
                                  This bundle should contain the ReactOnRails.register() code

    socket_server_path          string (Only used with mode `external`)
                                  Location of the socket to communicate with a dummy node.js server.
                                  Socket type must be acceptable by php function stream_socket_client.
                                  Example unix://node.sock, tcp://127.0.0.1:5000
                                  More info: http://php.net/manual/en/function.stream-socket-client.php
                                  Example of node server:
                                  https://github.com/Limenius/symfony-react-sandbox/blob/master/app/Resources/node-server/server.js

    logger                      \Psr\Log\LoggerInterface
                                  Logger used for errors
```

Rendering can be overridden on component basis, see https://github.com/Limenius/ReactRenderer

# Credits

This project is a Silex adaption of [Nacho Martín's](https://github.com/nacmartin) great [ReactBundle](https://github.com/Limenius/ReactBundle).

Both ReactBundle and this provider build on top of [React On Rails](https://github.com/shakacode/react_on_rails), and uses [`react-on-rails`](https://www.npmjs.com/package/react-on-rails) to render React components. Don't worry you don't need and won't see any Ruby when using this package. 
