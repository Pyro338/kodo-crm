<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 16.05.2018
 * Time: 12:10
 */

namespace App\Classes\Socket\Base;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class BasePusher implements WampServerInterface
{
    protected $subcribed_topics = [];

    /**
     * @return array
     */
    public function getSubcribedTopics()
    {
        return $this->subcribed_topics;
    }

    public function addSubscribedTopic($topic){
        $this->subcribed_topics[$topic->getId()] = $topic;
    }

    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $this->addSubscribedTopic($topic);
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        // TODO: Implement onUnSubscribe() method.
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo ('New connection ('.$conn->resourceId.')'.PHP_EOL);
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo ('Connection ('.$conn->resourceId.') has disconnected'.PHP_EOL);
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        $conn->close();
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo ('Error: '.$e->getMessage().PHP_EOL);
        $conn->close();
    }
}