<?php
namespace AE\Models\Base;

/**
 * Class LightState
 * @package AE\Models
 * @var $light_state_id INTEGER
 * @var $light_id INTEGER
 * @var $created DATETIME
 * @var $colourData TEXT
 */
class LightState extends BaseStoredObject
{
    protected $_table = "lights_states";
    public $light_state_id;
    public $light_id;
    public $created;
    public $colourData;

    public function save($automatic_reload = true)
    {
        if (!$this->created) {
            $this->created = date("Y-m-d H:i:s");
        }
        if (is_object($this->colourData) || is_array($this->colourData)) {
            $this->colourData = json_encode($this->colourData, JSON_PRETTY_PRINT);
        }
        parent::save($automatic_reload);
    }

    /**
     * @return bool|float|string|integer
     */
    private function getColourData($element)
    {
        if (!is_array($this->colourData) && !is_object($this->colourData)) {
            $decoded = json_decode($this->colourData, true);
            if ($decoded !== null) {
                $this->colourData = $decoded;
            }
        }
        return isset($this->colourData[$element]) ? $this->colourData[$element] : false;
    }

    /**
     * @return bool|float
     */
    public function getRed()
    {
        return $this->getColourData('red');
    }

    /**
     * @return bool|float
     */
    public function getGreen()
    {
        return $this->getColourData('green');
    }

    /**
     * @return bool|float
     */
    public function getBlue()
    {
        return $this->getColourData('blue');
    }

    /**
     * @return bool|float
     */
    public function getWhite()
    {
        return $this->getColourData('white');
    }

    /**
     * @return bool|float
     */
    public function getBrightness()
    {
        return $this->getColourData('brightness');
    }
}
