<?php

namespace BuiltInConnectors\Connectors\R7;

require_once __DIR__ . "/Resources/Routing.php";
require_once __DIR__ . "/Resources/Services.php";
require_once __DIR__ . "/ConnectorDefinition.php";

use BuiltInConnectors\Connectors\R7\ConnectorDefinition;
use BuiltInConnectors\Connectors\R7\Resources\Routing;
use BuiltInConnectors\Connectors\R7\Resources\Services;
use Common\BaseBundle;

class Bundle extends BaseBundle
{

    protected $bundle_root = __DIR__;
    protected $bundle_namespace = __NAMESPACE__;
    protected $routes = [];
    protected $services = [];

    public function init()
    {
        $routing = new Routing();
        $this->routes = $routing->getRoutes();
        $this->routing_prefix = "bundle/connectors/" . (new ConnectorDefinition())->definition["simple_name"] . $routing->getRoutesPrefix();
        $this->initRoutes();

        $this->services = (new Services())->getServices();
        $this->initServices();

    }

    public function getDefinition(){
      return (new ConnectorDefinition())->definition;
    }

    public function getConfiguration(){
      return (new ConnectorDefinition())->configuration;
    }

}
