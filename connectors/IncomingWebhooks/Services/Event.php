<?php


namespace BuiltInConnectors\Connectors\IncomingWebhooks\Services;
use http\Env\Response;
use  BuiltInConnectors\Connectors\IncomingWebhooks\Entity\InCommingWebhooksChannelMessage;

class Event
{

    public function __construct($app) {
        $this->main_service = $app->getServices()->get("connectors.common.main");
    }

    function proceedWebHook($id_url, $random, $content){
        $this->main_service->setConnector("incoming_webhooks");

        $entity = $this->main_service->getDocument($id_url);
        
        $group_id = $entity["group_id"];
        $message["channel_id"] = $entity["channel_id"];

        $content_json = json_encode($content->request->all());
        $content_array = json_decode($content_json, 1);

        $text = $content_array["text"];
        if(!$text && is_string($content_array["message"])){
            $text = $content_array["message"];
        }
        if(!$text && is_string($content_array["content"])){
            $text = $content_array["content"];
        }
        if(!$text && is_string($content_array["data"])){
            $text = $content_array["data"];
        }
        if(!$text && is_string($content_array["value"])){
            $text = $content_array["value"];
        }
        if(!$text && is_string($content_array["payload"])){
            $text = $content_array["payload"];
        }
        if(!$text){
            $text = $content_array;
        }

        $title = $content_array["title"];
        if(!$title && is_string($text)){
            $title = substr($text, 0, 80).(strlen($text)>80?"...":"");
        }

        $fallback_string = isset($content->get("text")["fallback_string"])?$content->get("text")["fallback_string"]:("Incoming webhook".($title?(" : ".$title):""));

        if(is_string($text)){
            $fallback_string .= " : " . $text;
        }

        if(is_array($text)){
            $fallback_string .=  json_encode($text);
        }

        $fallback_string = substr($fallback_string, 0, 120).(strlen($fallback_string)>120?"...":"");


        $formatted = is_string($text)?Array(Array("type"=>"compile", "content"=>$text)):$text;

        $custom_format = false;
        if(is_array($text) && isset($text["twacode"])){
            $formatted = $text["twacode"];
            $custom_format = true;
        }else if(is_array($text)){
            $formatted = Array(
                Array("type"=>"mcode", "content"=>json_encode($text))
            );
        }

        if(!$custom_format){
            //Try to find image in alert
            preg_match_all('/"https?:[a-zA-Z0-9-.\/\\\\_]+\.(png|jpg|jpeg|gif)"/i', $content_json, $matches);
            if(count($matches[0]) > 0){
                $formatted[] = Array("type"=>"br");
            }
            foreach ($matches[0] as $match){
                $image_url = json_decode($match);
                $formatted[] = Array("type"=>"image", "src"=>$image_url);
            }
        }


        if ($entity["random"] == $random){
            $message["content"] = Array(
                "fallback_string" => $fallback_string,
                "formatted" => $formatted
            );

        }else{
            return "ERROR : le lien demandé n'existe pas ";
        }

        $message["hidden_data"] = Array(
            "allow_delete" => "everyone",
            "custom_icon"=> is_string($content->get("icon"))?$content->get("icon"):null,
            "custom_title"=> is_string($title)?$title:"Incoming webhook"
        );
        $data_string = Array(
            "group_id" => $group_id,
            "message" => $message
        );
        $this->main_service->postApi("messages/save", $data_string);
    }

    public function ephemeralEvent($type, $event, $data){
        $this->main_service->setConnector("incoming_webhooks");

        $group = $data["group"];
        $entity = null;
        $user = $data["user"];

        if($type== "configuration" && $event == "channel"){
            $channel = $data["channel"];
            //Verification de la presence du channel

            $entity = $this->main_service->getDocument($channel["id"]);
            if (!$entity) {
                //insert
                $randomString = $this->main_service->generateToken();
                $this->main_service->saveDocument($channel["id"], [
                    "random" => $randomString,
                    "channel_id" => $channel["id"],
                    "group_id" => $group["id"]
                ]);
            }

            $url = $this->main_service->getServerBaseUrl()."incoming_webhooks/hook/".$channel["id"]."_".$entity["random"];

            $message = Array("type"=>"system","content"=>[
                "Use this link to post messages in this discussion.",
                Array("type"=>"br"),
                Array("type"=>"copiable", "content"=>"$url"),
                Array("type"=>"br"),
                Array("type"=>"br"),
                "Send JSON content to this link using a POST method and the following fields:",
                Array("type"=>"br"),
                Array("type"=>"compile", "content"=>
                    "```\n{\n\"text\": \"string or twacode object (under twacode key)\",\n\"icon\": \"icon url (optional)\",\n\"title\":\"string (optional)\"\n}\n```"),
                Array("type"=>"br"),
                Array("type"=>"button", "style"=>"default", "action_id"=>"cancel", "content"=>"Close")
            ]
            );

            $data_string = Array(
                "group_id" => $group["id"],
                "user_id" => $user["id"],
                "connection_id" =>$data["connection_id"],
                "form" => $message
            );
            $this->main_service->postApi("general/configure", $data_string);
        }
        if($type == "interactive_configuration_action" && $event == "cancel"){

            $data_string = Array(
                "group_id" => $group["id"],
                "user_id" => $user["id"],
                "connection_id" =>$data["connection_id"]
            );
            $this->main_service->postApi("general/configure_close", $data_string);
        }


    }


}
