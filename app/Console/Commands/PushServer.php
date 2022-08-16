<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Classes\Socket\Pusher;
use React\EventLoop\Factory as ReactLoop;
use React\ZMQ\Context as ReactContext;
use React\Socket\Server as ReactServer;
use Ratchet\Wamp\WampServer;
use Illuminate\Support\Facades\Config;

class PushServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push_server:serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push server started';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $loop    = ReactLoop::create();
        $pusher  = new Pusher;
        $context = new ReactContext($loop);
        $ip      = Config::get('app.ip');
        echo($ip);

        $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
        $pull->bind('tcp://' . $ip . ':5555');
        $pull->on('message', [$pusher, 'broadcast']);

        $web_sock = new ReactServer('tcp://0.0.0.0:8080', $loop);
        $web_server = new IoServer(
            new HttpServer(
                new WsServer(
                    new WampServer($pusher)
                )
            ),
            $web_sock
        );

        $this->info('Push server is running');
        $loop->run();
    }
}
