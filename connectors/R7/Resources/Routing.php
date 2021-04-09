<?php

namespace BuiltInConnectors\Connectors\R7\Resources;

use Common\BaseRouting;

class Routing extends BaseRouting
{
    protected $routing_prefix = "/";
    protected $routes = [
      "{mode}/save" => ["handler" => "Index:save", "methods" => ["GET", "POST"]],
      "{mode}/new" => ["handler" => "Index:new", "methods" => ["GET", "POST"]],
      "{mode}/open" => ["handler" => "Index:open", "methods" => ["GET", "POST"]],
      "{mode}/read" => ["handler" => "Index:read", "methods" => ["GET", "POST"]],
      "load" => ["handler" => "Index:load", "methods" => ["GET", "POST"]],
      "empty" => ["handler" => "Index:empty", "methods" => ["GET"]],
      "{mode}" => ["handler" => "Index:editor", "methods" => ["GET"]],
    ];
}
