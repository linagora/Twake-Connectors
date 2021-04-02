<?php


namespace BuiltInConnectors\Connectors\R7\Controller;

use BuiltInConnectors\Connectors\R7\Entity\OnlyofficeFile;
use BuiltInConnectors\Connectors\R7\Entity\OnlyofficeFileKeys;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;



class OnlyOfficeController extends Controller
{


    public function editorAction(Request $request, $mode)
    {
        $data = $this->get("only_office.event")->editorAction($request, $mode, $this->get('session'));
        if ($data)
            return $this->render('@OnlyOffice/Default/index.html.twig', $data);
        else
            return $this->render('@OnlyOffice/Default/error.html.twig', Array());

    }


    /**
     * Save / open files
     */
    public function saveAction(Request $request, $mode)
    {

        return $this->get("only_office.event")->saveAction($request, $mode);
    }

    public function openAction(Request $request, $mode)
    {
        return $this->get("only_office.event")->openAction($request, $mode, $this->get('session'));

    }

    public function readAction(Request $request, $mode)
    {
        return $this->get("only_office.event")->readAction($request, $mode);
    }


    public function loadAction(Request $request){

        $data =  $this->get("only_office.event")->loadAction($request, $this->get('session'));

        if(!is_array($data)){
            return $data;
        }

        return $this->redirect("/only_office/".$data["mode"]."/editor?workspace_id=".$data["workspace_id"]."&group_id=".$data["group_id"]."&file_id=".$data["file_id"]."&preview=".$data["preview"]);

    }



}
