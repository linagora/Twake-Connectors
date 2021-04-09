<?php

namespace BuiltInConnectors\Connectors\R7\Controller;

use Common\BaseController;
use Common\Http\Response;
use Common\Http\Request;
use BuiltInConnectors\Connectors\Jitsi\ConnectorDefinition;

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

    public function empty()
    {
      $extension = preg_replace("/[^a-z]+/", "", $request->query->all()["extension"]);
      $route = realpath(__DIR__."/../Resources/medias/empty." . $extension);
      $filename = basename($route);
      return new Response(file_get_contents($route));
    }

    public function editorAction(Request $request, $mode)
    {
        $data = $this->get("connectors.r7.event")->editorAction($request, $mode, $this->get('session'));
        if ($data)
            return $this->render('@OnlyOffice/Default/index.html.twig', $data);
        else
            return $this->render('@OnlyOffice/Default/file_error.html.twig', Array());
    }
    
    public function saveAction(Request $request, $mode)
    {
        return $this->get("connectors.r7.event")->saveAction($request, $mode);
    }

    public function openAction(Request $request, $mode)
    {
        return $this->get("connectors.r7.event")->openAction($request, $mode, $this->get('session'));
    }

    public function readAction(Request $request, $mode)
    {
        return $this->get("connectors.r7.event")->readAction($request, $mode);
    }

    public function loadAction(Request $request){
        $data =  $this->get("connectors.r7.event")->loadAction($request, $this->get('session'));
        if(!is_array($data)){
            return $data;
        }
        return $this->redirect(rtrim($this->get("connectors.common.main")->getServerBaseUrl(), "/") . "/r7_office/".$data["mode"]."/editor?workspace_id=".$data["workspace_id"]."&group_id=".$data["group_id"]."&file_id=".$data["file_id"]."&preview=".$data["preview"]);
    }

}
