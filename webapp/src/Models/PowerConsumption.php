<?php
namespace AE\Models;

use \Thru\ActiveRecord\ActiveRecord;

/**
 * Class PowerConsumption
 * @package DevExercize\Models
 * @var $power_reading_id INTEGER
 * @var $watts INTEGER
 * @var $created DATETIME
 */
class PowerConsumption extends ActiveRecord
{
    protected $_table = "power";
    public $power_reading_id;
    public $watts;
    public $created;

    public function save($automatic_reload = true)
    {
        if (!$this->created) {
            $this->created = date("Y-m-d H:i:s");
        }
        parent::save($automatic_reload);
    }
}
