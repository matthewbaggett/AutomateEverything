<?php
namespace AE\Tests\Lights;

use AE\Models\MiLightLightEndEffector as Bulb;

class MiLightTest extends \AE\Tests\BaseTest
{

    public function testCreateNewBulb()
    {
        $bulb = new Bulb();
        $bulb->state = Bulb::STATE_AVAILABLE;
        $bulb->type = Bulb::TYPE_RGBW;
        $bulb->lightSettings = json_encode([]);
        $bulb->save();

        $this->assertNotNull($bulb->light_id);

        return $bulb->light_id;
    }

    /**
     * @depends testCreateNewBulb
     * @param $light_id
     * @returns Bulb
     */
    public function testLoadNewBulb($light_id)
    {

        /** @var Bulb $loadedBulb */
        $loadedBulb = Bulb::search()->where('light_id', $light_id)->execOne();

        $this->assertEquals(Bulb::STATE_AVAILABLE, $loadedBulb->state);
        $this->assertEquals(Bulb::TYPE_RGBW, $loadedBulb->type);
        $this->assertNotFalse(json_decode($loadedBulb->lightSettings));

        return $loadedBulb;
    }

    /**
     * @depends testLoadNewBulb
     * @param Bulb $bulb
     */
    public function testSetColourState(Bulb $bulb)
    {
        $red = $this->faker->numberBetween(0, 1000) / 1000;
        $green = $this->faker->numberBetween(0, 1000) / 1000;
        $blue = $this->faker->numberBetween(0, 1000) / 1000;
        $brightness = $this->faker->numberBetween(0, 1000) / 1000;

        $bulb->setColourState($red, $green, $blue, $brightness);

        $this->assertEquals($red, $bulb->getColourState()->getRed());
        $this->assertEquals($green, $bulb->getColourState()->getGreen());
        $this->assertEquals($blue, $bulb->getColourState()->getBlue());
        $this->assertEquals(false, $bulb->getColourState()->getWhite());
        $this->assertEquals($brightness, $bulb->getColourState()->getBrightness());
    }


    /**
     * @depends testLoadNewBulb
     * @param Bulb $bulb
     */
    public function testSetWhiteState(Bulb $bulb)
    {
        $kelvin = $this->faker->numberBetween(2000, 6500);
        $brightness = $this->faker->numberBetween(0, 1000) / 1000;

        $bulb->setWhiteState($kelvin, $brightness);

        $this->assertEquals(false, $bulb->getColourState()->getRed());
        $this->assertEquals(false, $bulb->getColourState()->getGreen());
        $this->assertEquals(false, $bulb->getColourState()->getBlue());
        $this->assertEquals($kelvin . "K", $bulb->getColourState()->getWhite());
        $this->assertEquals($brightness, $bulb->getColourState()->getBrightness());
    }
}
