<?php
//namespace Application\Entity\Attribute\Value\Value;
namespace Concrete\Package\ThinkStory\Entity\Attribute\Value\Value;

use Concrete\Core\Entity\Attribute\Value\Value\AbstractValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="atTimbre")
 */
class TimbreValue extends AbstractValue
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $customLabel = '';

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $valid = 0;

    /**
     * @return mixed
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * @param mixed $valid
     */
    public function setValid($valid)
    {
        $this->valid = $valid;
    }

    /**
     * @return mixed
     */
    public function getCustomLabel()
    {
        return $this->customLabel;
    }

    /**
     * @param mixed $customLabel
     */
    public function setCustomLabel($customLabel)
    {
        $this->customLabel = $customLabel;
    }

    public function getValue(){
        return array($this->valid, $this->customLabel);
    }
    
    public function __toString(){
        return serialize(array($this->valid, $this->customLabel));
        //return (string) $this->valid;
        //return "1";
    }
}