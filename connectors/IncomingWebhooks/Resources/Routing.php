<?php

namespace BuiltInConnectors\Connectors\IncomingWebhooks\Resources;

use Common\BaseRouting;

class Routing extends BaseRouting
{

    protected $routing_prefix = "/";

    protected $routes = [
      "icon" => ["handler" => "Index:icon", "methods" => ["GET"]],
      "event" => ["handler" => "Index:event", "methods" => ["POST"]],
      "hook" => ["handler" => "Index:hook", "methods" => ["POST"]]
    ];

}
