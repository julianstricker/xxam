<?php
namespace Xxam\CommBundle\Services;
use BIT\RatchetBundle\Exception\RatchetBadServerInterfaceException;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use React\EventLoop\Factory as ReactFactory;
use React\EventLoop\LoopInterface;
use React\Socket\Server as ReactServer;
use Ratchet\Server\IoServer as RatchetIoServer;
use Ratchet\Http\HttpServer as RatchetHttpServer;
use Ratchet\WebSocket\WsServer as RatchetWsServer;
use Ratchet\Wamp\WampServer as RatchetWampServer;
use Ratchet\Wamp\WampServerInterface;
class XxamServerService
{
    private $webSocketConf;
    private $listenersConf;
    private $logger;
    public function __construct(array $webSocketConf, array $listenersConf, LoggerInterface $logger)
    {
        $this->webSocketConf = $webSocketConf;;
        $this->listenersConf = $listenersConf;
        $this->logger = $logger;
    }
    private function startWebSocketServer(LoopInterface $loop, OutputInterface $output, $container)
    {
        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new ReactServer($loop);
        $webSock->listen($this->webSocketConf['port'], $this->webSocketConf['host']);
        foreach ($this->listenersConf as $listenerName => $listenerConf) {
            $serverClassName = $listenerConf["class"];
            $listener = new $serverClassName($container);
            if ($listener instanceof WampServerInterface) {
                $ratchetWampServer = new RatchetWampServer($listener);
                $ratchetWsServer = new RatchetWsServer($ratchetWampServer);
                $ratchetHttpServer = new RatchetHttpServer($ratchetWsServer);
                new RatchetIoServer($ratchetHttpServer, $webSock);
                // write logs
                $this->logger->info(sprintf("Listener %s registered", $listenerName));
            } else {
                throw new RatchetBadServerInterfaceException($serverClassName);
            }
        }
        // write logs
        $statusMessage = sprintf(
            "Starting WebSocket Server on: %s:%s",
            $this->webSocketConf['host'],
            $this->webSocketConf['port']
        );
        $output->writeln($statusMessage);
        $this->logger->info($statusMessage);
    }
    public function start(OutputInterface $output,$container)
    {
        $loop = ReactFactory::create();
        $this->startWebSocketServer($loop, $output,$container);
        $loop->run();
    }
}