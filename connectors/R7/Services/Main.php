<?php


namespace BuiltInConnectors\Connectors\R7\Services;


use ConnectorsBundle\Services\MainConnectorService;

class Main extends MainConnectorService
{

    protected $app_name = "r7_office";

    public function __construct($app) {
        $this->main_service = $app->getServices()->get("connectors.common.main");
    }

}
