<?php


namespace BuiltInConnectors\Connectors\Rss\Services;


use RssBundle\Entity\RssLink;
use RssBundle\Entity\RssSubscribe;

class Event
{

    public function __construct($app) {
        $this->main_service = $app->getServices()->get("connectors.common.main");
    }

    public function simplified_link($url){
        $link = strtolower($url);
        $link = preg_replace("/^https?:\/\//", '', $link);
        $link = preg_replace("/^www\./", '', $link);
        $link = preg_replace("/\/$/", '', $link);
        $link = preg_replace("/\.[a-z0-9]$/", '', $link);
        $link = preg_replace("/\/?index$/", '', $link);
        return $link;
    }

    private function displayArticle($item){
        $article = array(
            'type' => 'attachment',
            'content' =>
                array(
                    array(
                        'type' => 'url',
                        'url' => $item->link."",
                        'content' => strip_tags($item->title),
                    ),
                    array(
                        'type' => 'br',
                    ),
                    array(
                        'type' => 'system',
                        'content' => strip_tags($item->description),
                    )
                )
        );

        //if article contains images than we add it to the message contant
        if (isset($item->enclosure)) {
            $article["content"][] = array(
                'type' => 'br',
            );
            $article["content"][] = array(
                'type' => 'image',
                'src' => $item->enclosure->attributes()->url->__toString(),
            );
        }

        return $article;
    }

    private function getXml($url){
        if(strpos($url, "http://") === false && strpos($url, "https://") === false && strpos($url, "rss://") === false){
            return false;
        }
        $xml = $this->main_service->get($url, true);
        if(!$xml){
            return false;
        }

        $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOWARNING);

        //Retrieve atom format
        if($xml->entry){
            $xml = json_decode(json_encode($xml), 1);
            $item = [];
            foreach($xml["entry"] as $entry){
              $entry["link"] = $entry["link"]["@attributes"]["href"];
              $entry["description"] = $entry["content"];
              $item[] = $entry;
            }
            $xml["channel"] = [
              'title' => $xml["title"],
              'item' => $item
            ];
            $xml = json_decode(json_encode($xml));
          }

        //check if link is corresponding to rss
        if(!isset($xml->channel)){
            return false;
        }

        return $xml;
    }

    public function checkRss(){

        // get data from Rss link and Rss subscribe
        $data_links = $this->doctrine->getRepository("RssBundle:RssLink")->findBy(Array() , Array() , 50 , 0);

        //browse data recover from rss link
        foreach ($data_links as $data_link){

            //modify timestamp of last check
            $this->doctrine->remove($data_link);
            $this->doctrine->flush();
            $data_link->setLastCheck(date("U"));
            $this->doctrine->persist($data_link);
            $this->doctrine->flush();

            //getting data from the rss link as xml
            $xml = $this->getXml($data_link->getLink());

            $new_articles = [];
            $titles = [];

            if($xml) {
                $data_link->setFailed(0);

                $read = false;

                foreach ($xml->channel->item as $v) {

                    //if too much articles or timestamp of current article is inferior than current timestamp, we quit this rss and pass to the next link
                    if (count($new_articles)>10 || strtotime($v->pubDate . "") < $data_link->getLastRead()){
                        break;
                    }
                    //if not we read the current article and we initialized $read to true
                    $read = true;

                    $new_articles[] = $this->displayArticle($v);
                    $titles[] = $v->title;

                }

                //if we have read data from this rss link , then we change time of last read
                if ($read) {
                    $data_link->setLastRead(date("U"));
                    $this->doctrine->persist($data_link);
                    $this->doctrine->flush();

                    $data_subscribes = $this->doctrine->getRepository("RssBundle:RssSubscribe")->findBy(Array("comparison_link"=>$data_link->getComparisonLink()));
                    foreach($data_subscribes as $data_subscribe) {

                        $message = Array(
                            "channel_id" => $data_subscribe->getChannelId()
                        );

                        $message["content"] = [
                            "fallback_string"=> "RSS : New on ".$data_subscribe->getTitle()." - ".join(", ", $titles).".",
                            "formatted" => [["type"=>"system", "content"=>[
                                    ["type"=>"emoji", "content"=>"newspaper"],
                                    " New on ",
                                    ["type"=>"bold", "content"=>$data_subscribe->getTitle()]
                                ]],
                            ],

                        ];

                        $message["content"]["formatted"] = array_merge($message["content"]["formatted"], $new_articles);
                        $message["hidden_data"] = Array("allow_delete" => "everyone");

                        $data_string = Array(
                            "group_id" => $data_subscribe->getGroupId(),
                            "message" => $message
                        );

                        //sharing the message
                        $message["hidden_data"] = Array("allow_delete" => "everyone");
                        $this->main_service->postApi("messages/save", $data_string);

                    }
                }

            }else{
                $data_link->setFailed($data_link->getFailed()+1);
                if($data_link->getFailed() > 10){
                    //remove link
                    $this->doctrine->remove($data_link);
                    //remove subscribes
                    $data_subscribes = $this->doctrine->getRepository("RssBundle:RssSubscribe")->findBy(Array("comparison_link"=>$data_link->getComparisonLink()));
                    foreach($data_subscribes as $data_subscribe){
                        $this->doctrine->remove($data_subscribe);
                    }
                    $this->doctrine->flush();

                }
            }

        }


    }


    private function addRss($data , $user , $channel, $group , $url, $message ){

        if(isset($data["message"]["ephemeral_id"])){
            $parent_message = Array("id"=>$data["message"]["parent_message_id"]);
        }else{
            $parent_message = $data["parent_message"];
        }

        //check existing rss on the current channel
        $entity = $this->doctrine->getRepository("RssBundle:RssSubscribe")->findOneBy(Array("channel_id"=>$channel["id"] , "comparison_link" =>$this->simplified_link($url)));


        if($entity == null){

            $xml = $this->getXml($url);

	    

            if(!$xml){
error_log("unable to read xml");            
    return false;
            }

            $flux = $xml->channel;

            //Add Rss On Rss_subscribe
            $entity = new RssSubscribe($channel["id"], $group["id"], $url , $this->simplified_link($url));
            $entity->setTitle($flux->title?$flux->title:$url);
            $this->doctrine->persist($entity);

	    $title = $entity->getTitle() . "";
	error_log("title: ".$title);

            //check existing rss in any channel
            $rss_links = $this->doctrine->getRepository("RssBundle:RssLink")->findBy(Array("comparison_link" => $this->simplified_link($url)));
            //Add Rss On Rss_Link if not exist
            if($rss_links == array()) {
                $rss_link = new RssLink($url , $this->simplified_link($url) ,date("U")  , 1);
                $rss_link->setLastRead(date("U"));
                $this->doctrine->persist($rss_link);
            }else{
                $rss_link = $rss_links[0];
                $rss_link->setInstances($rss_link->getInstances() + 1);
                $this->doctrine->persist($rss_link);
            }
            $this->doctrine->flush();

            $message["content"]= Array(
                ["type"=>"system", "content"=>[
                    "@".$user["username"]." added an RSS feed."
                ]
                ],
                ["type"=>"attachment", "content"=>[
                    ["type"=>"bold", "content" => $title],
                    ["type"=>"br"],
                    ["type"=>"system", "content"=> (($flux->description && $flux->description!=$flux->title)?$flux->description:"")." ".$url]
                ]
                ]
            );

	   error_log(json_encode($message["content"]));


        }else{

            $message= Array(
                "channel_id" => $channel["id"],
                "content" => Array(
                    ["type"=>"system", "content"=>"This RSS feed is already attached to this channel."],
                    ["type"=>"br"],
                    ["type"=>"button", "style"=>"default", "action_id"=>"cancel", "content"=>"Close"]
                ),
                "parent_message_id" => isset($parent_message["id"])?$parent_message["id"]:"",
                "ephemeral_message_recipients" => [$user["id"]]
            );


            $message["_once_ephemeral_message"] = true;

        }

        return $message;
    }



    private function createListEphemeral($data , $user , $channel){

        if(isset($data["message"]["ephemeral_id"])){
            $parent_message = Array("id"=>$data["message"]["parent_message_id"]);
        }else{
            $parent_message = $data["parent_message"];
        }

        $finallist = array();
        $entities = $this->doctrine->getRepository("RssBundle:RssSubscribe")->findBy(Array("channel_id"=>$channel["id"]) );

        if($entities == null) {
            $list = ['type' => 'system', 'content' => "No RSS feed has been added to this channel."];
            array_push($finallist, $list);
            array_push($finallist, Array("type"=>"br"));
        }

        foreach($entities as $entity){

            $list =array (
                'type' => 'attachment',
                'content' =>
                    array (
                        array(
                            'type' => 'bold',
                            'content' =>
                                array(
                                    'type' => 'system',
                                    'content' => $entity->getTitle(),
                                ),
                        ),
                        ["type"=>"br"],
                        array(
                            'type' => 'system',
                            'content' => $entity->getLink()." "
                        ),
                        array (
                            'type' => 'button',
                            'style' => 'danger',
                            'inline' => "true" ,
                            "action_id" => "remove_".$entity->getId(),
                            'content' => 'remove',
                        ),
                        Array("type"=>"br"),
                    ),
            );

            array_push($finallist , $list);


        }



        $message = Array(
            "channel_id" => $channel["id"],
            "content" => Array(
                Array("type"=>"system","content"=>$finallist),
                Array("type"=>"button", "style"=>"default", "action_id"=>"cancel", "content"=>"Close")
            ),
            "parent_message_id" => isset($parent_message["id"])?$parent_message["id"]:"",
            "ephemeral_message_recipients" => [$user["id"]]
        );


        if(isset($data["message"]["ephemeral_id"])){
            $message["ephemeral_id"] = $data["message"]["ephemeral_id"];
        }else{
            $message["_once_ephemeral_message"] = true;
        }




        return $message;
    }



    public function proceedEvent($type, $event, $data){



        $group = $data["group"];
        $user = $data["user"];
        $channel = $data["channel"];
        $command = $data["command"];



        if($type== "action" && $event == "command") {
            $message = $data["messsage"];
            $message["channel_id"] = $channel["id"];
            $message["sender"] = $user["id"];


            if (explode(" ", $command)[0] == "add") {
                $url = explode(" ", $command)[1];

                $message= $this->addRss($data, $user, $channel , $group , $url, $message);
            }
            if (explode(" ", $command)[0] == "list") {
                $message = $this->createListEphemeral($data, $user, $channel);
            }

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $message
            );

            $message["hidden_data"] = Array("allow_delete" => "everyone");

            $this->main_service->postApi("messages/save", $data_string);
        }


        if($type == "interactive_message_action" && $event == "cancel"){

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $data["message"]
            );
            $this->main_service->postApi("messages/remove", $data_string);

            return;

        }



        //remove
        if($type == "interactive_message_action" && explode("_", $event)[0] == "remove"){
            $i = explode("_", $event)[1];
            $message = $data["message"];

            //Deleting rss from channel
            $repository = $this->doctrine->getRepository("RssBundle:RssSubscribe");
            $entity = $repository->findOneBy(Array("id"=>$i));
            $this->doctrine->remove($entity);
            $rss_url_simplified = $entity->getComparisonLink();
            $this->doctrine->flush();

            //Cheking if rss is available in another channel
            $repository = $this->doctrine->getRepository("RssBundle:RssLink");
            $entity = $repository->findOneBy(Array("comparison_link"=>$rss_url_simplified));
            if($entity->getInstances() == 1) {
                $this->doctrine->remove($entity);
                $this->doctrine->flush();
            }else{

                $entity->setInstances($entity->getInstances() - 1);
                $this->doctrine->persist($entity);
                $this->doctrine->flush();

            }


            $_message = $this->createListEphemeral($data, $user, ["id"=>$message["channel_id"]]);
            $_message["ephemeral_id"] = $message["ephemeral_id"];

            $data_string = Array(
                "group_id" => $group["id"],
                "message" => $_message
            );


            $this->main_service->postApi("messages/save", $data_string);

            return;

        }



    }


}
