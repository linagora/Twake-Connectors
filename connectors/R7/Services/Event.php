<?php


namespace BuiltInConnectors\Connectors\R7\Services;

use BuiltInConnectors\Connectors\R7\ConnectorDefinition;
use BuiltInConnectors\Connectors\R7\Entity\OnlyofficeFile;
use BuiltInConnectors\Connectors\R7\Entity\OnlyofficeFileKeys;
use Common\Http\Response;
use Common\Http\Request;


class Event
{

    public function __construct($app) {
        $this->main_service = $app->getServices()->get("connectors.common.main");
        $this->app = $app;
    }

    public function setConfiguration(){
        $configuration = (new ConnectorDefinition())->configuration;
        $this->r7_domain = $this->app->getContainer()->getParameter("defaults.connectors.r7_office.domain", $configuration["domain"]);

        $this->APIPUBLICKEY_SLIDE = $this->app->getContainer()->getParameter("defaults.connectors.r7_office.apipubkey_slide", $configuration["apipubkey_slide"]);
        $this->APIPUBLICKEY_SPREADSHEET = $this->app->getContainer()->getParameter("defaults.connectors.r7_office.apipubkey_spreadsheet", $configuration["apipubkey_spreadsheet"]);
        $this->APIPUBLICKEY_TEXT = $this->app->getContainer()->getParameter("defaults.connectors.r7_office.apipubkey_text", $configuration["apipubkey_text"]);
    }

    public function getParametersForMode($mode)
    {
        $this->setConfiguration();

        switch ($mode) {
            case "presentation":
            case "slide":
                $mode = "presentation";
                $name = "Presentation";
                $color = "aa5252";
                $defaultExtension = ".pptx";
                $apikey = $this->APIPUBLICKEY_SLIDE;
                break;
            case "spreadsheet":
                $mode = "spreadsheet";
                $name = "Spreadsheet";
                $color = "40865c";
                $defaultExtension = ".xlsx";
                $apikey = $this->APIPUBLICKEY_SPREADSHEET;
                break;
            default:
                $mode = "text";
                $name = "Document";
                $color = "446995";
                $defaultExtension = ".docx";
                $apikey = $this->APIPUBLICKEY_TEXT;
                break;
        }

        $this->APIPUBLICKEY = $apikey;

        return Array(
            "mode" => $mode,
            "color" => $color,
            "key" => $apikey,
            "name" => $name,
            "defaultExtension" => $defaultExtension
        );
    }


    public function editorAction(Request $request, $mode, $session = null)
    {
        $this->main_service->setConnector("r7_office");
        $this->setConfiguration();
        
        $parameters = $this->getParametersForMode(explode("/",$mode)[0]);
        $workspace_id = $request->query->all()["workspace_id"];
        $group_id = $request->query->all()["group_id"];
        $file_id = $request->query->all()["file_id"];
        $preview = $request->query->all()["preview"];

        $user_id = $this->getSession('user_id');
        
        $user = null;
        if($user_id) {
            $user = $this->main_service->postApi("users/get", array("user_id" => $user_id, "group_id" => $group_id), 60);
            error_log(json_encode($user));
            if (!$user){
              return array();
            }
        }


        if ($user != null && isset($user["object"])) {

            $data_file =  $this->main_service->postApi("drive/find", array("workspace_id" => $workspace_id, "group_id" => $group_id, "element_id" => $file_id, "user_id" => $user_id), 60);
            error_log(json_encode($data_file));
            if (!$data_file || !isset($data_file["object"]))
              return array();


            $data = Array();
            $data["userid"] = $user_id;
            $data["username"] = $user["object"]["username"];
            $data["language"] = $user["object"]["language"];
            $data["userimage"] =  $user["object"]["thumbnail"] ;
            $data["mode"] = $parameters["mode"];

            $r7_domain =  $this->r7_domain;

            $data["onlyoffice_server"] = $r7_domain;
            $data["defaultExtension"] = $parameters["defaultExtension"];
            $data["color"] = $parameters["color"];
            $data["modeName"] = $parameters["name"];
            $data["workspaceId"] = $workspace_id;
            $data["server"] = $this->main_service->getServerBaseUrl();
            $data["file_id"] = $file_id;
            $data["filename"] = $data_file["object"]["name"];
            $data["groupid"] = $group_id;
            $data["fileType"] = $data_file["object"]["extension"];
            $data["preview"] = $preview?"true":"false";


            return $data;

        }

        return array();

    }


    /**
     * Save / open files
     */
    public function saveAction(Request $request)
    {
        $this->main_service->setConnector("r7_office");
        $this->setConfiguration();
        
        $fToken = $request->query->get("token");
        $group_id = $request->query->get("groupId");
        $file_id = $request->query->get("fileId");
        $request = $request->request->all();

        if ($request["status"] == "2") {

            $key = $request["key"];
            $document = $request["url"];

            $fileKey = $this->main_service->getDocument("file_keys_" . $file_id);

            if ($fileKey != null) {
                $file = $this->main_service->getDocument("file_" . $file_id . "_" . $fToken);

                if ($file != null) {
                    $oldFilename = $fileKey["name"];
                    $oldFileParts = explode(".", $oldFilename);
                    array_pop($oldFileParts);
                    $newExtension = array_pop(explode(".", $document));
                    $newName = join(".", $oldFileParts) . "." . $newExtension;

                    $fileKey["key"] = bin2hex(random_bytes(64));
                    $this->main_service->saveDocument("file_keys_" . $file_id, $fileKey);

                    $url = $document;
                    //IMPORTANT ! Disable local files !!!
                    if (strpos($url, "http://") !== false) {
                        $url = "http://" . str_replace("http://", "", $url);
                    } else {
                        $url = "https://" . str_replace("https://", "", $url);
                    }

                    if (!$url) {

                        return new Response(Array("error" => 1));

                    } else {
                        $data = array(
                            "group_id" => $group_id,
                            "object" => array(
                                "id" => $file["file_id"],
                                "name" => $newName,
                            ),
                            "file_url" => $url

                        );

                        $this->main_service->postApi("drive/save", $data, 60);
                        return new Response();

                    }

                }

            }

        }

        return new Response(Array("error" => 0));
    }

    public function openAction(Request $request, $session=null)
    {
        $this->main_service->setConnector("r7_office");
        $this->setConfiguration();
        
        $user_id = $this->getSession('user_id');

        if ($request->request->all()["user_id"] == $user_id && $user_id) {

            $workspaceId = $request->request->all()["workspaceId"];
            $filename = $request->request->all()["filename"];
            $fId = $request->request->get("file_id");
            $groupId = $request->request->get("groupId");


            $data_file =  $this->main_service->postApi("drive/find", array("workspace_id" => $workspaceId, "group_id" => $groupId, "element_id" => $fId, "user_id" => $user_id), 60);
            if (!$data_file || !isset($data_file["object"]))
                return new Response(array("error" => "file not found"));

            $file = [
                "workspace_id" => $workspaceId,
                "file_id" => $fId,
                "token" => base64_encode(bin2hex(random_bytes(20))),
                "date" => date("U"),
            ];
            $this->main_service->saveDocument("file_" . $file["file_id"] . "_" . $file["token"], $file);

            $fileKey = $this->main_service->getDocument("file_keys_" . $fId);
            if (!$fileKey) {
                $fileKey = [
                    "workspace_id" => $workspaceId,
                    "file_id" => $fId,
                    "key" => bin2hex(random_bytes(64))
                ];
            }
            $fileKey["name"] = $filename;
            $this->main_service->saveDocument("file_keys_" . $fId, $fileKey);

            return new Response(Array(
                "token" => $file["token"],
                "key" => $fileKey["key"],
                "file_id" => $fId,
                "filename" => $filename
            ));

        }
        return new Response();
    }

    public function readAction(Request $request, $mode)
    {
        $this->main_service->setConnector("r7_office");
        $this->setConfiguration();
        
        $this->getParametersForMode($mode);

        $fToken = $request->query->all()["fileToken"];
        $fId = $request->query->all()["fileId"];
        $group_id = $request->query->all()["groupId"];

        $file = $this->main_service->getDocument("file_" . $fId . "_" . $fToken);

        if ($file != null) {

            if ($file["date"] > (new \DateTime())->getTimestamp() - 60 * 60) {

                $file["date"] = (new \DateTime())->getTimestamp();
                $this->main_service->saveDocument("file_" . $fId . "_" . $fToken, $file);

                $data = array(
                    "workspace_id" => $file["workspace_id"],
                    "group_id" => $group_id,
                    "file_id" => $file["file_id"]

                );

                echo $this->main_service->postApi("drive/download", $data, 60, true);
                die();

            }

            $this->main_service->saveDocument("file_" . $fId . "_" . $fToken, null);

        }

        return new Response();

    }


    public function loadAction(Request $request, $session = null){
        $this->main_service->setConnector("r7_office");
        $this->setConfiguration();
        
        $token = $request->query->all()["token"];
        $file_id = $request->query->all()["file_id"];
        $workspace_id = $request->query->all()["workspace_id"];
        $group_id = $request->query->all()["group_id"];
        $preview = $request->query->all()["preview"];


        $token_identity =  $this->main_service->postApi("core/token", array("token" => $token), 60);
        error_log(json_encode($token_identity));
        if (!$token_identity)
            return new Response(array("error" => "Invalid token"));


        $user_id = $token_identity["user_id"];

        $this->setSession('user_id', $user_id);

        $data_file =  $this->main_service->postApi("drive/find", array("workspace_id" => $workspace_id, "group_id" => $group_id, "element_id" => $file_id), 60);
        if (!$data_file)
            return new Response(array("error" => "file not found"));

        $categorie1 = ["docx", "doc", "docm", "dot", "dotm", "dotx", "epub", "fodt", "mht", "odt", "pdf", "rtf", "txt", "djvu", "xps"];
        $categorie2 = ["pptx", "fodp", "odp", "pot", "potm", "potx", "pps", "ppsm", "ppsx", "ppt", "pptm"];
        $categorie3 = ["xlsx", "csv", "fods", "ods", "xls", "xlsm", "xlt", "xltm", "xltx"];

        if (in_array($data_file["object"]["extension"], $categorie1))
            $mode = "text";
        else if (in_array($data_file["object"]["extension"], $categorie2))
            $mode = "slide";
        else if (in_array($data_file["object"]["extension"], $categorie3))
            $mode = "spreadsheet";
        else
            $mode = "text";

        //$mode = "slide";

        return array(
            "mode" => $mode,
            "workspace_id" => $workspace_id,
            "group_id" => $group_id,
            "file_id" => $file_id,
            "preview" => $preview
        );


    }


    private function getSession($key) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION["r7_office_".$key];
    }

    private function setSession($key, $value) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION["r7_office_".$key] = $value;
    }


}
