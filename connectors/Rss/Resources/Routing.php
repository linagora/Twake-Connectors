<?php

namespace BuiltInConnectors\Connectors\Rss\Resources;

use Common\BaseRouting;

class Routing extends BaseRouting
{

    protected $routing_prefix = "/";

    protected $routes = [
      "icon" => ["handler" => "Index:icon", "methods" => ["GET"]],
      "event" => ["handler" => "Index:event", "methods" => ["POST"]],
    ];

}
