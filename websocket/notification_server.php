#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../db.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\SecureServer;
use React\Socket\Server;

class NotificationWebSocket implements \Ratchet\MessageComponentInterface {
    protected $clients;
    protected $userConnections;
    protected $pdo;

    public function __construct($pdo) {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        $this->pdo = $pdo;
    }

    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if ($data['type'] === 'auth') {
            // Store user connection mapping
            $this->userConnections[$data['userId']] = $from;
            echo "User {$data['userId']} authenticated\n";
        }
    }

    public function onClose(\Ratchet\ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Remove user connection mapping
        foreach ($this->userConnections as $userId => $connection) {
            if ($connection === $conn) {
                unset($this->userConnections[$userId]);
                break;
            }
        }
    }

    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function broadcastNotification($userId, $notification) {
        if (isset($this->userConnections[$userId])) {
            $connection = $this->userConnections[$userId];
            $connection->send(json_encode([
                'type' => 'notification',
                'data' => $notification
            ]));
        }
    }
}

// Create event loop and socket server
$loop = Factory::create();
$webSocket = new NotificationWebSocket($pdo);

// Create server socket
$socket = new Server('0.0.0.0:8080', $loop);

$server = new IoServer(
    new HttpServer(
        new WsServer($webSocket)
    ),
    $socket,
    $loop
);

echo "WebSocket server started on port 8080\n";
$loop->run(); 