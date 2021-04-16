<?php


namespace BuiltInConnectors\Connectors\IncomingWebhooks\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="in_comming_webhooks_channel_message",options={"engine":"MyISAM", "scylladb_keys": {{"id": "ASC"},{"channel_id" : "ASC"} }})
 * @ORM\Entity(repositoryClass="BuiltInConnectors\Connectors\IncomingWebhooks\Repository\InCommingWebhooksChannelMessageRepository")
 */
class InCommingWebhooksChannelMessage
{
    /**
     * @ORM\Column(name="id", type="twake_timeuuid")
     * @ORM\Id
     */
    protected $id;

    /**
     * @ORM\Column(name="channel_id", type="twake_timeuuid")
     */
    protected $channel_id;

    /**
     * @ORM\Column(name="group_id", type="twake_timeuuid")
     */
    protected $group_id;

    /**
     * @ORM\Column(name="random", type="string")
     */
    protected $random;

    public function __construct($random, $channel_id, $group_id)
    {
        $this->group_id = $group_id;
        $this->setRandom($random);
        $this->channel_id = $channel_id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getChannelId()
    {
        return $this->channel_id;
    }

    /**
     * @param mixed $channel_id
     */
    public function setChannelId($channel_id)
    {
        $this->channel_id = $channel_id;
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * @param mixed $group_id
     */
    public function setGroupId($group_id)
    {
        $this->group_id = $group_id;
    }

    /**
     * @return mixed
     */
    public function getRandom()
    {
        return $this->random;
    }

    /**
     * @param mixed $random
     */
    public function setRandom($random)
    {
        $this->random = $random;
    }



}