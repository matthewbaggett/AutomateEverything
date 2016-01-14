<?php

namespace AE\Tests;

use Faker\Generator;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var Generator */
    protected $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = new Generator();
        $this->faker->addProvider(new \Faker\Provider\en_US\Person($this->faker));
        $this->faker->addProvider(new \Faker\Provider\en_US\Address($this->faker));
        $this->faker->addProvider(new \Faker\Provider\en_US\PhoneNumber($this->faker));
        $this->faker->addProvider(new \Faker\Provider\en_US\Company($this->faker));
        $this->faker->addProvider(new \Faker\Provider\Lorem($this->faker));
        $this->faker->addProvider(new \Faker\Provider\Internet($this->faker));
    }
}
