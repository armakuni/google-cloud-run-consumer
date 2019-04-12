<?php

use Armakuni\Demo\PhpInserter\ConfigFactory;
use Armakuni\Demo\PhpInserter\StackdriverExporterFactory;
use Armakuni\Demo\PhpInserter\TraceService;
use OpenCensus\Trace\Tracer;

require __DIR__ . "/vendor/autoload.php";


$googleConfig = (new ConfigFactory())->build();
$exporter = (new StackdriverExporterFactory($googleConfig))->build();
(new TraceService($exporter))->start();

$message = Tracer::inSpan(
    ['name' => 'consume-message'],
    function () {
        $rawMessage = file_get_contents('php://input');

        if ($rawMessage === false) {
            error_log("No body");
            return "";
        }

        $message = json_decode($rawMessage, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Invalid message: " . $rawMessage);
        }

        return $message;
    }
);

Tracer::inSpan(
    ['name' => 'render'],
    function () use ($message) {
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
        }

        http_response_code(204);

        $decodedMessage = base64_decode($message['message']['data']);

        if ($decodedMessage === false) {
            error_log("Could not base64 decode message:" . $message['message']['data']);
            return;
        }
        
        error_log($decodedMessage);
    }
);
