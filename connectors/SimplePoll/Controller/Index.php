<?php

namespace BuiltInConnectors\Connectors\SimplePoll\Controller;

use Common\Http\Response;
use Common\BaseController;
use Common\Http\Request;

class Index extends BaseController
{
    public function event(Request $request)
    {
        $data = $request->request->get("data");
        $event = $request->request->get("event");
        $type = $request->request->get("type");

        $this->get("connectors.simplepoll.event")->proceedEvent($type, $event, $data);

        return new Response("");
    }
}