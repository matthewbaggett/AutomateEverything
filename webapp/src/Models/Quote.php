<?php
namespace DevExercize\Models;

use \Thru\ActiveRecord\ActiveRecord;

/**
 * Class Quote
 * @package DevExercize\Models
 * @var $quote_id INTEGER
 * @var $ticker_code VARCHAR(16)
 * @var $value DECIMAL(12,6)
 * @var $created DATETIME
 */
class Quote extends ActiveRecord
{
    protected $_table = "ticker_quotes";
    public $quote_id;
    public $ticker_code;
    public $value = 0;
    public $created;

    public function save($automatic_reload = true)
    {
        if (!$this->created) {
            $this->created = date("Y-m-d H:i:s");
        }
        parent::save($automatic_reload);
    }
}
