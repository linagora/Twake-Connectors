<?php


namespace BuiltInConnectors\Connectors\SimplePoll\Services;

use  BuiltInConnectors\Connectors\SimplePoll\Entity\SimplePollMessage;


class Event
{

    public function __construct($app) {
        $this->main_service = $app->getServices()->get("connectors.common.main");
    }

    private function createEphemeral($question, $responses, $data, $user){
        $this->main_service->setConnector("simplepoll");

        $responsesGraph = array();
        for ($i = 0; $i < count($responses); $i++) {
            $button =Array("type"=>"bold","content"=> Array("type" => "compile", "content" => $responses[$i]." "));
            array_push($responsesGraph, $button);
            array_push($responsesGraph,Array("type"=>"br"));
        }
        if(isset($data["message"]["ephemeral_id"])){
            $channel = Array("id"=>$data["message"]["channel_id"]);
            $parent_message = Array("id"=>$data["message"]["parent_message_id"]);
        }else{
            $channel = $data["channel"];
            $parent_message = $data["parent_message"];
        }

        $message = Array(
            "channel_id" => $channel["id"],
            "parent_message_id" => isset($parent_message["id"])?$parent_message["id"]:"",
            "content" => Array(
                Array("type"=>"attachment","content"=>[
                    Array("type"=>"bold", "content"=>Array("type"=>"bold", "content"=>Array("type" => "compile", "content" => $question." "))),
                    Array("type"=>"br"),
                    Array("type"=>"system", "content"=>"Tap send to make your survey accessible to everyone.")
                ]
                ),
                Array("type"=>"attachment","content"=>[
                    Array("type"=>"bold", "content"=>Array("type"=>"bold", "content"=>$responsesGraph)),
                    Array("type"=>"system", "content"=>"0 votes")
                ]),
                Array("type"=>"button", "style"=>"primary", "action_id"=>"send", "content"=>"Send"),
                Array("type"=>"button", "style"=>"default", "action_id"=>"cancel", "content"=>"Cancel")
            ),
            "hidden_data" => Array(
                "question" => $question,
                "responses" => $responses,
                "votes" => Array()
            ),
            "ephemeral_message_recipients" => [$user["id"]]
        );


        if(isset($data["message"]["ephemeral_id"])){
            $message["ephemeral_id"] = $data["message"]["ephemeral_id"];
        }else{
            $message["_once_ephemeral_message"] = true;
        }
        return $message;
    }

    function comparator($object1, $object2) {
        return $object1->progress > $object2->progress;
    }

    private function updateMessage($creator, $question, $responses, $votes = Array()){
        $this->main_service->setConnector("simplepoll");

        $votes_buttons = Array();
        $progressBar = Array();
        $tmp = 0;

        $total_votes = 0;
        foreach ($votes as $vote) {
            $total_votes += count($vote);

        }
        foreach ($responses as $i => $response) {
            $percentage = 0;
            if (!isset($votes[$i])) {
                $votes[$i] = Array();
            } else {
                if ($total_votes > 0)
                    $percentage = intval(100 * count($votes[$i]) / $total_votes);

            }
            array_push($votes_buttons, Array("type" => "button", "style" => "default", "action_id" => "vote_$i", "content" =>
                Array("type" => "compile", "content" =>$response)));
                
            array_push($progressBar, Array("type" => "bold", "content" =>
                Array("type" => "compile", "content" =>$response)));
            array_push($progressBar, Array("type" => "br"));
            array_push($progressBar, Array("type" => "progress_bar", "progress" => $percentage));
            array_push($progressBar, Array("type" => "system", "content" => " ".$percentage . "%"));
            array_push($progressBar, Array("type" => "br"));

        }

        $message = Array(
            "content" => Array(
                Array("type"=>"system", "content" => "@".$creator["username"]." created a poll." ),
                Array("type"=>"br"),
                Array("type" => "attachment", "content" => [
                    Array("type" => "bold", "content" => Array("type" => "compile", "content" => $question)),
                    Array("type" => "br"),
                    Array("type" => "system", "key" => "reVote" , "content" => Array("type" => "bold", "content" => $votes_buttons))]),
                Array("type" => "attachment", "content" => [
                    Array("type" => "bold", "content" => $progressBar),
                    Array("type" => "system", "content" => $total_votes . " votes")
                ])

            ),
            "hidden_data" => Array(
                "votes" => $votes,
                "question" => $question,
                "responses" => $responses,
                "creator" => $creator
            )
        );

        return $message;

    }

    public function proceedEvent($type, $event, $data){
        $this->main_service->setConnector("simplepoll");

        $group = $data["group"];
        $user = $data["user"];

        if(isset($data["message"]["id"]) && $data["message"]["id"]){
            $data["message"]["hidden_data"] = $this->main_service->getDocument($data["message"]["id"]);
        }

        if($type == "interactive_message_action" && explode("_", $event)[0] == "voteagain"){
            $i = explode("_", $event)[1];
            $user = $data["user"];
            $message = $data["message"];
//            error_log(print_r($data["message"],true));
            $i = intval($i);
            $question = $message["hidden_data"]["question"];
            $responses = $message["hidden_data"]["responses"];
            $votes = $message["hidden_data"]["votes"];
            $creator = $message["hidden_data"]["creator"];
            $key = array_search($user["id"],$votes[$i]);
            unset($votes[$i][$key]);
            $messageUpdated = $this->updateMessage($creator, $question, $responses, $votes);
            $messageUpdated["_once_user_specific_update"] = Array(
                "user_id" => $user["id"],
                "modifiers" => Array(),
                "replace_all" => true
            );
            $messageUpdated["id"] = $message["id"];
            $messageUpdated["channel_id"] = $message["channel_id"];
            $messageUpdated["parent_message_id"] = $message["parent_message_id"];

            $this->main_service->saveDocument($message["id"], $messageUpdated["hidden_data"]);
            $choices = $messageUpdated["hidden_data"];

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $messageUpdated
            );
            $this->main_service->postApi("messages/save", $data_string);

        }
        //Voter
        if($type == "interactive_message_action" && explode("_", $event)[0] == "vote"){
            $i = explode("_", $event)[1];
            $user = $data["user"];
            $message = $data["message"];
            $i = intval($i);

            $question = $message["hidden_data"]["question"];
            $responses = $message["hidden_data"]["responses"];
            $votes = $message["hidden_data"]["votes"];
            $creator = $message["hidden_data"]["creator"];
            foreach ($votes as $key => $vote){
                foreach ($vote as $v => $id){
                    if ($id == $user["id"]){
                        unset($votes[$key][$v]);
                    }
                }
            }

            Array_push($votes[$i], $user["id"]);


            $messageUpdated = $this->updateMessage($creator, $question, $responses, $votes);
            $messageUpdated["_once_user_specific_update"] = Array(
                "user_id" => $user["id"],
                "modifiers" => Array(
                    "reVote" => Array("You voted for : ",
                        Array("type" => "bold", "content" => Array("type" => "compile", "content" => $responses[$i]." ")),
                        Array("type"=>"button", "style"=>"default", "inline" => true, "action_id"=>"voteagain_$i", "content"=>"Vote again")
                    )
                )
            );
            $messageUpdated["id"] = $message["id"];
            $messageUpdated["channel_id"] = $message["channel_id"];
            $messageUpdated["parent_message_id"] = $message["parent_message_id"];

            $this->main_service->saveDocument($message["id"], $messageUpdated["hidden_data"]);
            $choices = $messageUpdated["hidden_data"];

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $messageUpdated
            );
            $this->main_service->postApi("messages/save", $data_string);
        }

        if($type == "interactive_message_action" && $event == "send"){

            //Delete ephemeral message
            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $data["message"]
            );
            $this->main_service->postApi("messages/remove", $data_string);

            error_log(json_encode($data["message"]["hidden_data"]));

            //Generate definitive message
            $channel = Array("id"=>$data["message"]["channel_id"]);
            $parent_message = Array("id"=>$data["message"]["parent_message_id"]);
            $question = $data["message"]["hidden_data"]["question"];
            $responses = $data["message"]["hidden_data"]["responses"];
            $creator = $data["user"];
            $message = $this->updateMessage($creator, $question, $responses);
            $message["channel_id"] = $channel["id"];
            $message["parent_message_id"] = isset($parent_message["id"])?$parent_message["id"]:"";
            $message["sender"] = $user["id"];

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $message
            );
            $response = $this->main_service->postApi("messages/save", $data_string);
            $id = $response["object"]["id"];
            $hidden_data = $response["object"]["hidden_data"];
            $this->main_service->saveDocument($id, $hidden_data);

        }

        if($type == "interactive_message_action" && $event == "cancel"){

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $data["message"]
            );
            $this->main_service->postApi("messages/remove", $data_string);
        }

        if($type== "action" && $event == "command"){
            $command = explode('"', $data["command"]);
            $list = [];
            foreach($command as $element){
                $element = trim($element);
                if($element){
                    $list[] = $element;
                }
            }
            $question = array_shift($list);
            $responses = $list;

            $message = $this->createEphemeral($question, $responses, $data, $user);

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $message
            );

            $this->main_service->postApi("messages/save", $data_string);

        }
    }


}