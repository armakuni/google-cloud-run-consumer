<?php


namespace Armakuni\Demo\PhpInserter;


use OpenCensus\Trace\Exporter\StackdriverExporter;

class StackdriverExporterFactory
{

    private $googleConfig;

    public function __construct($googleConfig)
    {
        $this->googleConfig = $googleConfig;
    }

    public function build()
    {
        $exporter = new StackdriverExporter(
            [
                'clientConfig' => $this->googleConfig,
            ]
        );

        return $exporter;
    }
}
