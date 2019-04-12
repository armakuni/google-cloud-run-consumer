<?php


namespace Armakuni\Demo\PhpInserter;


use OpenCensus\Trace\Exporter\ExporterInterface;
use OpenCensus\Trace\Integrations\Curl;
use OpenCensus\Trace\Integrations\Grpc;
use OpenCensus\Trace\Tracer;

class TraceService
{
    /**
     * @var ExporterInterface
     */
    private $exporter;

    public function __construct(ExporterInterface $exporter)
    {
        $this->exporter = $exporter;
    }

    public function start()
    {
        Grpc::load();
        Curl::load();
        Tracer::start($this->exporter);
    }
}
