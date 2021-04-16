<?php


namespace BuiltInConnectors\Connectors\Giphy\Services;


class Event
{

    public function __construct($app)
    {
        $this->main_service = $app->getServices()->get("connectors.common.main");
    }

    public function setConfiguration(){
        $configuration = (new ConnectorDefinition())->configuration;
        $this->domain = $this->app->getContainer()->getParameter("defaults.connectors.giphy.domain", $configuration["domain"]);
        $this->apikey = $this->getParameter("defaults.connectors.giphy.apikey", $configuration["apikey"]);
    }

    public function proceedEvent($type, $event, $data) {
        $this->main_service->setConnector("giphy");

        $giphy_url = $this->domain;
        $giphy_key = $this->apikey;

        $group = $data["group"];
        $user = $data["user"];
        if(isset($data["message"]["ephemeral_id"])){
            $parent_message = Array("id"=>$data["message"]["parent_message_id"]);
        }else{
            $parent_message = $data["parent_message"];
        }

        if($type == "interactive_message_action" && $event == "cancel"){

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $data["message"]
            );

            $this->main_service->postApi("messages/remove", $data_string);

        }

        if($type == "interactive_message_action" && $event == "send"){

            $channel = Array("id"=>$data["message"]["channel_id"]);
            $url = $data["message"]["hidden_data"]["url"];
            $keywords = $data["message"]["hidden_data"]["query"];

            if(!$url){
                die();
            }

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $data["message"]
            );
            $this->main_service->postApi("messages/remove", $data_string);

            $message = Array(
                "channel_id" => $channel["id"],
                "parent_message_id" => isset($parent_message["id"])?$parent_message["id"]:"",
                "content" => Array(
                    Array("type"=>"system", "content"=>["@".$user["username"]." sent a GIF ", Array("type"=>"bold", "content"=> "#".$keywords)]),
                    Array("type"=>"br"),
                    Array("type"=>"image", "src"=>$url),
                ),
                "sender" => $user["id"],
            );

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $message
            );

            $this->main_service->postApi("messages/save", $data_string);

        }

        if(($type == "interactive_message_action" && $event == "shuffle")
            || ($type == "action" && ( $event == "open" || $event == "command" ))
        ){

            $gif_i = 0;
            if(isset($data["message"]["ephemeral_id"])){
                $channel = Array("id"=>$data["message"]["channel_id"]);
                $command = $data["message"]["hidden_data"]["query"];
                $gif_i = $data["message"]["hidden_data"]["i"];
            }else{
                $channel = $data["channel"];
                if($data["command"]){
                    $command = $data["command"];
                }else{
                    $command = "gif";
                }
            }

            $from = array(" ","#","/");
            $to   = array("+","+","+");
            $newquery = str_replace($from, $to, $command);
            $gurl = $giphy_url."gifs/search?api_key=".$giphy_key."&q=".$newquery."&limit=25&offset=0&rating=G&lang=en";
            $jsongifs = $this->main_service->get($gurl);

            $nbgif = count($jsongifs["data"]);
            $gif_i = (($gif_i?$gif_i:0) + 1) % $nbgif;
            $url = $jsongifs["data"][$gif_i]["images"]["downsized_medium"]["url"];

            $message = Array(
                "channel_id" => $channel["id"],
                "content" => Array(
                    Array("type"=>"system", "content"=>["Tap Shuffle to propose another image for ", Array("type"=>"bold", "content"=>$command)]),
                    Array("type"=>"br"),
                    Array("type"=>"image", "src"=>$url),
                    Array("type"=>"br"),
                    Array("type"=>"button", "style"=>"default", "action_id"=>"cancel", "content"=>"Cancel"),
                    Array("type"=>"button", "style"=>"default", "action_id"=>"shuffle", "content"=>"Shuffle"),
                    Array("type"=>"button", "style"=>"primary", "action_id"=>"send", "content"=>"Send")
                ),
                "hidden_data" => Array(
                    "url" => $url,
                    "query" => $command,
                    "i" => $gif_i
                ),
                "parent_message_id" => isset($parent_message["id"])?$parent_message["id"]:"",
                "ephemeral_message_recipients" => [$user["id"]]
            );

            if(isset($data["message"]["ephemeral_id"])){
                $message["ephemeral_id"] = $data["message"]["ephemeral_id"];
            }else{
                $message["_once_ephemeral_message"] = true;
            }

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $message
            );

            $this->main_service->postApi("messages/save", $data_string);

        }
    }

}
