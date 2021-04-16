<?php

namespace BuiltInConnectors\Connectors\IncomingWebhooks\Resources;

use Common\BaseServices;

class Services extends BaseServices
{
    protected $services = [
      "connectors.incoming_webhooks.event" => "Event"
    ];

}
