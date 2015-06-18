<?php
/**
 * @author Thomas Johnell <tjohnell@gmail.com>
 * @date   6/17/2015
 */

namespace SpeciesSim\Species;

/**
 * Class Specie
 *
 * @package SpeciesSim\Specie
 */
class Species
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $monthly_food_consumption;

    /**
     * @var int
     */
    protected $monthly_water_consumption;

    /**
     * @var int
     */
    protected $life_span;

    /**
     * @var int
     */
    protected $minimum_breeding_age;

    /**
     * @var int
     */
    protected $maximum_breeding_age;

    /**
     * @var int
     */
    protected $gestation_period;

    /**
     * @var int
     */
    protected $minimum_temperature;

    /**
     * @var int
     */
    protected $maximum_temperature;

    public function __construct(
        $name,
        $monthly_food_consumption,
        $monthly_water_consumption,
        $life_span,
        $minimum_breeding_age,
        $maximum_breeding_age,
        $gestation_period,
        $minimum_temperature,
        $maximum_temperature
    ) {
        $this->name                      = $name;
        $this->monthly_food_consumption  = $monthly_food_consumption;
        $this->monthly_water_consumption = $monthly_water_consumption;
        $this->life_span                 = $life_span;
        $this->minimum_breeding_age      = $minimum_breeding_age;
        $this->maximum_breeding_age      = $maximum_breeding_age;
        $this->gestation_period          = $gestation_period;
        $this->minimum_temperature       = $minimum_temperature;
        $this->maximum_temperature       = $maximum_temperature;
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
    public function getMinimumBreedingAge()
    {
        return $this->minimum_breeding_age;
    }

    /**
     * @return int
     */
    public function getMaximumBreedingAge()
    {
        return $this->maximum_breeding_age;
    }

    /**
     * @return int
     */
    public function getGestationPeriod()
    {
        return $this->gestation_period;
    }

    /**
     * @return int
     */
    public function getMonthlyWaterConsumption()
    {
        return $this->monthly_water_consumption;
    }

    /**
     * @return int
     */
    public function getMonthlyFoodConsumption()
    {
        return $this->monthly_food_consumption;
    }

    /**
     * @return int
     */
    public function getLifeSpan()
    {
        return $this->life_span;
    }

    /**
     * @return int
     */
    public function getMaximumTemperature()
    {
        return $this->maximum_temperature;
    }

    /**
     * @return int
     */
    public function getMinimumTemperature()
    {
        return $this->minimum_temperature;
    }
}
