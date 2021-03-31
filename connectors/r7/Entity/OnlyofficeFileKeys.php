<?php


namespace OnlyOfficeBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * OnlyofficeFile
 *
 * @ORM\Table(name="onlyofficefilekeys",options={"engine":"MyISAM" , "scylladb_keys": {{"id": "ASC"}, {"file_id": "ASC"}, {"key": "ASC"}}})
 * @ORM\Entity(repositoryClass="OnlyOfficeBundle\Repository\OnlyofficeRepository")
 */
class OnlyofficeFileKeys
{
    /**
     * @ORM\Column(name="id", type="twake_timeuuid")
     * @ORM\Id
     */
    private $id;


    /**
     * @ORM\Column(name="workspace_id", type="twake_timeuuid")
     */
    private $workspaceId;

    /**
     * @ORM\Column(name="file_id", type="twake_timeuuid")
     */
    private $fileId;

    /**
     * @ORM\Column(name="key", type="twake_text", length=512)
     */
    private $key;

    /**
     * @ORM\Column(name="name", type="twake_text")
     */
    private $name;


    public function __construct($workspaceId, $fileId)
    {
        $this->fileId = $fileId;
        $this->workspaceId = $workspaceId;
        $this->newKey();
    }


    /**
     * @param mixed $key
     */
    public function newKey()
    {
        $this->key = bin2hex(random_bytes(64));
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
    public function getWorkspaceId()
    {
        return $this->workspaceId;
    }

    /**
     * @param mixed $workspaceId
     */
    public function setWorkspaceId($workspaceId)
    {
        $this->workspaceId = $workspaceId;
    }

    /**
     * @return mixed
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @param mixed $fileId
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }





}
