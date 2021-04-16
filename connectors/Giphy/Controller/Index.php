<?php

namespace BuiltInConnectors\Connectors\Giphy\Controller;

use Common\BaseController;
use Common\Http\Request;
use Common\Http\Response;

class Index extends BaseController
{

    public function event(Request $request)
    {
        $data = $request->request->get("data");
        $event = $request->request->get("event");
        $type = $request->request->get("type");

        $this->get('connectors.giphy.event')->proceedEvent($type, $event, $data);

        return new Response("ok");
    }
}
