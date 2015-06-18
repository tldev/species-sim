<?php
/**
 * @author Thomas Johnell <tjohnell@gmail.com>
 * @date   6/17/2015
 */

namespace SpeciesSim\Habitat;

/**
 * Class Habitat
 *
 * @package SpeciesSim\Habitat
 */
class Habitat
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $monthly_food;

    /**
     * @var int
     */
    protected $monthly_water;

    /**
     * @var int
     */
    protected $average_temperature_summer;

    /**
     * @var int
     */
    protected $average_temperature_spring;

    /**
     * @var int
     */
    protected $average_temperature_fall;

    /**
     * @var int
     */
    protected $average_temperature_winter;

    /**
     * @param string $name
     * @param int    $monthly_food
     * @param int    $monthly_water
     * @param int    $average_temperate_summer
     * @param int    $average_temperature_spring
     * @param int    $average_temperature_fall
     * @param int    $average_temperature_winter
     */
    public function __construct(
        $name,
        $monthly_food,
        $monthly_water,
        $average_temperate_summer,
        $average_temperature_spring,
        $average_temperature_fall,
        $average_temperature_winter
    ) {
        $this->name                       = $name;
        $this->monthly_food               = $monthly_food;
        $this->monthly_water              = $monthly_water;
        $this->average_temperature_summer = $average_temperate_summer;
        $this->average_temperature_spring = $average_temperature_spring;
        $this->average_temperature_fall   = $average_temperature_fall;
        $this->average_temperature_winter = $average_temperature_winter;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getMonthlyFood()
    {
        return $this->monthly_food;
    }

    /**
     * @return int
     */
    public function getMonthlyWater()
    {
        return $this->monthly_water;
    }

    /**
     * @param $month
     * @return int
     */
    public function getAverageTemperature($month)
    {
        if ($month < 0 || $month > 12) {
            throw new \InvalidArgumentException(
                sprintf('Month must be between 1 and 12, given: %s', $month)
            );
        }

        switch ($month) {
            case 12:
            case 1:
            case 2:
                $avg_temp = $this->average_temperature_winter;
                break;
            case 3:
            case 4:
            case 5:
                $avg_temp = $this->average_temperature_spring;
                break;
            case 6:
            case 7:
            case 8:
                $avg_temp = $this->average_temperature_summer;
                break;
            case 9:
            case 10:
            case 11:
            default:
                $avg_temp = $this->average_temperature_fall;
        }

        return $avg_temp;
    }
}
