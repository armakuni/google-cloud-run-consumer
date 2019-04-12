<?php


namespace Armakuni\Demo\PhpInserter;


class ConfigFactory
{
    /**
     * @return array
     */
    public function build(): array
    {
        $googleConfig = [];

        if ($projectId = getenv('GOOGLE_PROJECT_ID')) {
            $googleConfig['projectId'] = $projectId;
        }

        return $googleConfig;
    }
}
