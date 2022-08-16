<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 16.05.2018
 * Time: 10:43
 */

namespace App\Classes\Socket;

use App\Classes\Socket\Base\BaseSocket;
use Ratchet\ConnectionInterface;

class ChatSocket extends BaseSocket
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        echo 'New connection ('.$conn->resourceId.')\n';
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numResv = count($this->clients) - 1;

        echo sprintf('Connection %d sending message "%s" to %d other connection%s\n',
            $from->resourceId, $msg, $numResv, $numResv == 1 ? '' : 's');

        foreach ($this->clients as $client){
            if ($from !== $client){
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        echo 'Connection ('.$conn->resourceId.') has disconnected\n';
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo ('An error occured('.$e->getMessage().')\n');

        $conn->close();
    }
}