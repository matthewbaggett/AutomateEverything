<?php
namespace AE\Models\Base;

/**
 * Class LightEndEffector
 * @package AE\Models
 * @var $light_id INTEGER
 * @var $type ENUM("White","RGB","RGBW")
 * @var $state ENUM("Available","Missing","Disabled")
 */
abstract class LightEndEffector extends BaseEndEffector
{
    protected $_table = "lights";

    const TYPE_RGBW = 'RGBW';
    const TYPE_RGB = 'RGB';
    const TYPE_WHITE = 'White';

    const STATE_AVAILABLE = "Available";
    const STATE_MISSING = "Missing";
    const STATE_DISABLED = "Disabled";

    public $light_id;
    public $type;
    public $state;
    public $lightSettings;

    private $_light_state;

    public function setColourState(float $red, float $green, float $blue, float $brightness)
    {
        $newState = new LightState();
        $newState->light_id = $this->light_id;
        $newState->colourData = [
            'red' => $red,
            'green' => $green,
            'blue' => $blue,
            'brightness' => $brightness,
        ];
        $newState->save();
        $this->_light_state = $newState;
        return $this;
    }

    public function setWhiteState(int $temperatureKelvin, float $brightness)
    {
        $newState = new LightState();
        $newState->light_id = $this->light_id;
        $newState->colourData = [
            'white' => $temperatureKelvin . "K",
            'brightness' => $brightness,
        ];
        $newState->save();
        $this->_light_state = $newState;
        return $this;
    }

    public function getColourState(): LightState
    {
        if (!$this->_light_state) {
            $this->_light_state = LightState::search()
                ->where('light_id', $this->light_id)
                ->order('created', 'DESC')
                ->execOne();
        }
        return $this->_light_state;
    }
}
