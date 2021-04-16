<?php

namespace BuiltInConnectors\Connectors\IncomingWebhooks\Controller;

use Exception;
use Common\BaseController;
use Common\Http\Request;
use Symfony\Component\Routing\Annotation\Route;
use Common\Http\Response;

class Index extends BaseController
{

    public function icon()
    {
      $configuration = (new ConnectorDefinition())->definition;
      $route = realpath(__DIR__."/../Resources/medias/".$configuration["icon_url"]);

      $filename = basename($route);
      $file_extension = strtolower(substr(strrchr($filename,"."),1));

      switch( $file_extension ) {
          case "gif": $ctype="image/gif"; break;
          case "png": $ctype="image/png"; break;
          case "jpeg":
          case "jpg": $ctype="image/jpeg"; break;
          case "svg": $ctype="image/svg+xml"; break;
          default:
      }

      header('Content-type: ' . $ctype);

      return new Response(file_get_contents($route));
    }

    public function event(Request $request)
    {
        $data = $request->request->get("data");
        $event = $request->request->get("event");
        $type = $request->request->get("type");

        $this->get('connectors.incomming.event')->ephemeralEvent($type, $event, $data);

        return new Response("ok");
    }
    public function hook(Request $request, $id){
        $list = explode("_", $id);
        $hook_entity = $this->get('connectors.incomming.event')->proceedWebHook($list[0], $list[1], $request);
        return new Response($hook_entity);
    }

}
