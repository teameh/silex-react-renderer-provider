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

    public function register(Container $app)
    {
        $app['react.renderer'] = function () use ($app) {
            $conf = $app['react.serverside_rendering'];
            if (isset($conf['mode']) && $conf['mode'] === 'external') {
                return new ExternalServerReactRenderer(
                    $conf['socket_server_path'],
                    isset($conf['fail_loud']) ? $conf['fail_loud'] : $app['debug'],
                    isset($conf['logger']) ? $conf['logger'] : null
                );
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
