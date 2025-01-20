<?php
 
namespace App\Traits;
 
use DateTime;
use Doctrine\ORM\Mapping as ORM;
 
trait TimestampableTrait
{
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $createDt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $updateDt = null;
 
    public function getcreateDt(): ?DateTime
    {
        return $this->createDt;
    }
 
    public function setcreateDt(DateTime $createDt): self
    {
        $this->createDt = $createDt;
        return $this;
    }
 
    public function getupdateDt(): ?DateTime
    {
        return $this->updateDt;
    }
 
    public function setupdateDt(DateTime $updateDt): self
    {
        $this->updateDt = $updateDt;
        return $this;
    }
 
    public function updateTimestamps(): void
    {
        $this->updateDt = new DateTime();
 
        if ($this->createDt === null) {
            $this->createDt = new DateTime();
        }
    }
}
