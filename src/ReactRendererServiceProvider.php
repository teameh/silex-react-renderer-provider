<?php

/*
 * This file is part of teameh/silex-react-renderer-provider.
 *
 * (c) Tieme van Veen https://github.com/teameh
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Teameh\Silex\Services\React;

use Limenius\ReactRenderer\Renderer\ExternalServerReactRenderer;
use Limenius\ReactRenderer\Renderer\PhpExecJsReactRenderer;
use Limenius\ReactRenderer\Twig\ReactRenderExtension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

class ReactRendererServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function boot(Application $app)
    {
        $app['twig']->addExtension($app['react.extension']);
    }


    /**
     * @param Container $app
     *
     * Configuration options are:
     *
     * - react.default_rendering:      string, either 'client_side', 'server_side' or 'both'
     *
     * - react.serverside_rendering:
     *   - fail_loud                   bool, defaults to $app['debug']
     *                                   In case of error in server-side rendering, throw exception
     *
     *   - trace                       bool, defaults to $app['debug']
     *                                   Replay every console.log message produced during server-side rendering in the
     *                                   JavaScript console. Note that if enabled it will throw a (harmless) React warning
     *
     *   - mode                        string
     *                                   Mode can be `"phpexecjs"` (to execute Js from PHP using PhpExecJs),
     *                                   or `"external"` (to rely on an external node.js server) Default is `"phpexecjs"`
     *
     *   - string server_bundle_path   string (Only used with mode `phpexecjs`)
     *                                   Location of the server bundle, that contains the concatenated javascript bundle.
     *                                   This bundle should contain the ReactOnRails.register() code
     *
     *   - socket_server_path          string (Only used with mode `external`)
     *                                   Location of the socket to communicate with a dummy node.js server.
     *                                   Socket type must be acceptable by php function stream_socket_client.
     *                                   Example unix://node.sock, tcp://127.0.0.1:5000
     *                                   More info: http://php.net/manual/en/function.stream-socket-client.php
     *                                   Example of node server:
     *                                   https://github.com/Limenius/symfony-react-sandbox/blob/master/app/Resources/node-server/server.js
     *
     *   - logger                      \Psr\Log\LoggerInterface
     *                                   Logger used for errors
     *
     */
    public function register(Container $app)
    {
        $app['react.renderer'] = function () use ($app) {
            $conf = $app['react.serverside_rendering'];
            if (isset($conf['mode']) && $conf['mode'] === 'external') {

                if(!isset($conf['socket_server_path'])) {
                    throw new \InvalidArgumentException('socket_server_path should be set');
                }

                return new ExternalServerReactRenderer(
                    $conf['socket_server_path'],
                    isset($conf['fail_loud']) ? $conf['fail_loud'] : $app['debug'],
                    isset($conf['logger']) ? $conf['logger'] : null
                );
            }

            if(!isset($conf['server_bundle_path'])) {
                throw new \InvalidArgumentException('server_bundle_path should be set');
            }

            return new PhpExecJsReactRenderer(
                $conf['server_bundle_path'],
                isset($conf['fail_loud']) ? $conf['fail_loud'] : $app['debug'],
                isset($conf['logger']) ? $conf['logger'] : null
            );
        };

        $app['react.extension'] = function () use ($app) {
            $conf = $app['react.serverside_rendering'];

            return new ReactRenderExtension(
                $app['react.renderer'],
                isset($app['react.default_rendering']) ? $app['react.default_rendering'] : 'both',
                isset($conf['trace']) ? $conf['trace'] : $app['debug']
            );
        };
    }
}
