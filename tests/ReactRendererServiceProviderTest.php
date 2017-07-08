<?php

namespace Teameh\Silex\Services\React\Tests;

use Silex\Application;
use Teameh\Silex\Services\React\ReactRendererServiceProvider;

/**
 * Class PhpExecJsReactRendererTest
 */
class ReactRendererServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIncompleteConfiguration1()
    {
        $app = new Application();
        $app->register(new ReactRendererServiceProvider());
        $app['react.renderer']->render();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIncompleteConfiguration2()
    {
        $app = new Application();
        $app->register(new ReactRendererServiceProvider(), [
            'react.serverside_rendering' => []
        ]);
        $app['react.renderer']->render();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIncompleteConfiguration3()
    {
        $app = new Application();
        $app->register(new ReactRendererServiceProvider(), [
            'react.serverside_rendering' => [
                'mode' => 'external'
            ]
        ]);
        $app['react.renderer']->render();
    }

    public function testUseRightRenderer()
    {
        $app1 = new Application();
        $app1->register(new ReactRendererServiceProvider(), [
            'react.serverside_rendering' => [
                'server_bundle_path' => __DIR__ . '/Fixtures/server-bundle-react.js'
            ]
        ]);

        $this->assertInstanceof('Limenius\ReactRenderer\Renderer\PhpExecJsReactRenderer', $app1['react.renderer']);

        $app2 = new Application();
        $app2->register(new ReactRendererServiceProvider(), [
            'react.serverside_rendering' => [
                'mode' => 'external',
                'socket_server_path' => 'unix://node.sock'
            ]
        ]);

        $this->assertInstanceof('Limenius\ReactRenderer\Renderer\ExternalServerReactRenderer', $app2['react.renderer']);
    }

    public function testReactBundle()
    {
        $app = new Application();
        $app->register(new ReactRendererServiceProvider(), [
            'react.default_rendering' => 'server_side',
            'react.serverside_rendering' => [
                'server_bundle_path' => __DIR__ . '/Fixtures/server-bundle-react.js'
            ]
        ]);

        $expected = '<h1 data-reactroot="" data-reactid="1" data-react-checksum="-605941478">It Works!</h1>';
        $this->assertEquals($expected, $app['react.renderer']->render('MyApp', '{msg:"It Works!"}', 1, null, false));

        $expected .= "\n" . '<script id="consoleReplayLog">'."\n";
        $expected .= 'console.log.apply(console, ["[SERVER] RENDERED MyApp to dom node with id: 1 with railsContext:","{\"serverSide\":true}"]);'."\n";
        $expected .= '</script>';
        $this->assertEquals($expected, $app['react.renderer']->render('MyApp', '{msg:"It Works!"}', 1, null, true));
    }
}
