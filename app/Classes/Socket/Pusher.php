<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 16.05.2018
 * Time: 12:26
 */

namespace App\Classes\Socket;

use App\Classes\Socket\Base\BasePusher;
use ZMQContext;
use Illuminate\Support\Facades\Config;

class Pusher extends BasePusher
{
    static function sendDataToServer(array $data)
    {
        $context = new ZMQContext;
        $socket  = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my_pusher');
        $ip      = Config::get('app.ip');

        $socket->connect('tcp://' . $ip . ':5555');

        $data = json_encode($data);

        $socket->send($data);
    }

    public function broadcast($json_data_to_send)
    {
        $data_to_send      = json_decode($json_data_to_send);
        $subscribed_topics = $this->getSubcribedTopics();

        if(is_object($data_to_send)){
            if (isset($subscribed_topics[$data_to_send->topic_id])) {
                $topic = $subscribed_topics[$data_to_send->topic_id];
                $topic->broadcast($data_to_send);
            }
        }
    }
}