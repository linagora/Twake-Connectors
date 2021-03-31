<?php


namespace OnlyOfficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * OnlyofficeFile
 *
 * @ORM\Table(name="onlyofficefile",options={"engine":"MyISAM" , "scylladb_keys": {{"file_id": "ASC", "file_token": "ASC", "id": "ASC"}, {"file_token": "ASC"}}})
 * @ORM\Entity(repositoryClass="OnlyOfficeBundle\Repository\OnlyofficeRepository")
 */
class OnlyofficeFile
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
     * @ORM\Id
     */
    private $fileId;

    /**
     * @ORM\Column(name="date", type="twake_bigint")
     */
    private $date;

    /**
     * @ORM\Column(name="file_token", type="string", length=256)
     * @ORM\Id
     */
    private $token;


    public function __construct($workspaceId, $fileId)
    {
        $this->fileId = $fileId;
        $this->workspaceId = $workspaceId;
        $this->token = base64_encode(bin2hex(random_bytes(20)));
        $this->resetDate();
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
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }


    /**
     * @return mixed
     */
    public function resetDate()
    {
        $this->date = date("U");
    }



}
