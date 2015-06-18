<?php
/**
 * @author Thomas Johnell <tjohnell@gmail.com>
 * @date   6/17/2015
 */

namespace SpeciesSim\Simulator;

use SpeciesSim\Animal\Animal;
use SpeciesSim\Habitat\Habitat;
use SpeciesSim\Species\Species;

/**
 * Class World
 *
 * @package SpeciesSim\Simulator
 */
class World
{
    const DEATH_COUNT_STARVATION   = 'starvation';
    const DEATH_COUNT_THIRST       = 'thirst';
    const DEATH_COUNT_AGE          = 'age';
    const DEATH_COUNT_HOT_WEATHER  = 'hot_weather';
    const DEATH_COUNT_COLD_WEATHER = 'cold_weather';

    /**
     * @var Habitat
     */
    protected $habitat;

    /**
     * @var Species
     */
    protected $species;

    /**
     * @var int
     */
    protected $total_months;

    /**
     * @var int
     */
    protected $month_midpoint;

    /**
     * @var int
     */
    protected $month_tally = 0;

    /**
     * @var int
     */
    protected $current_month = 1;

    /**
     * @var Animal[]|\ArrayObject
     */
    protected $males;

    /**
     * @var Animal[]|\ArrayObject
     */
    protected $females;

    /**
     * @var int
     */
    protected $food = 0;

    /**
     * @var int
     */
    protected $water = 0;

    /**
     * @var int
     */
    protected $max_population = 0;

    /**
     * @var int
     */
    protected $avg_population = 0;

    /**
     * @var int
     */
    protected $mid_point_population;

    /**
     * @var array
     */
    protected $mortality_rates;

    /**
     * @var int
     */
    protected $master_month_tally = 0;

    protected $death_counts = [

        self::DEATH_COUNT_STARVATION   => 0,
        self::DEATH_COUNT_AGE          => 0,
        self::DEATH_COUNT_THIRST       => 0,
        self::DEATH_COUNT_COLD_WEATHER => 0,
        self::DEATH_COUNT_HOT_WEATHER  => 0
    ];

    /**
     * @param Habitat $habitat
     * @param Species $species
     * @param int     $total_years
     */
    public function __construct(Habitat $habitat, Species $species, $total_years)
    {
        $this->habitat        = $habitat;
        $this->species        = $species;
        $this->total_months   = $total_years * 12;
        $this->month_midpoint = $this->total_months / 2;
    }

    public function simulate($iterations)
    {
        for ($i = 0; $i < $iterations; $i++) {
            $this->init();

            while ($this->month_tally < $this->total_months) {
                $this->replenish();
                $this->giveBirth();
                $this->savePopulationData();  //Made sense to me to save population data after births but before deaths
                $this->breed();
                $this->eat();
                $this->drink();
                $this->aging();
                $this->fryOrFreeze();
                $this->death();
                $this->updateCurrentMonth();
                $this->month_tally++;
                $this->master_month_tally++;
            }

            $this->saveMortalityRate();
        }

        return $this->buildReport();
    }

    protected function buildReport()
    {
        $total_deaths      = array_sum($this->death_counts);
        $death_percentages = array();
        foreach ($this->death_counts as $name => $count) {
            $perc = round(($count / $total_deaths) * 100, 2);
            if ($perc == 100) {
                $perc = number_format($perc, 2); //Trailing zeros for 100.00%
            }
            $death_percentages[$name] = $perc;
        }
        return array(
            'max_pop'           => $this->max_population,
            'avg_pop'           => round($this->avg_population, 0),
            'mortality_rate'    => round(array_sum($this->mortality_rates) / count($this->mortality_rates), 2),
            'death_percentages' => $death_percentages
        );
    }

    protected function init()
    {
        $this->males   = new \ArrayObject();
        $this->females = new \ArrayObject();
        $this->males->append(new Animal($this->species, Animal::SEX_MALE));
        $this->females->append(new Animal($this->species, Animal::SEX_FEMALE));
        $this->food          = 0;
        $this->water         = 0;
        $this->month_tally   = 0;
        $this->current_month = rand(1, 12); //Random starting month
    }

    /**
     * @return void
     */
    protected function savePopulationData()
    {
        $total_current_population = $this->males->count() + $this->females->count();
        $this->setMaxPopulation($total_current_population);
        $this->setAvgPopulation($total_current_population);
        $this->setMidPointPopulation($total_current_population);
    }

    /**
     * @param int $total_current_population
     */
    protected function setMaxPopulation($total_current_population)
    {
        if ($total_current_population > $this->max_population) {
            $this->max_population = $total_current_population;
        }
    }

    /**
     * @param int $total_current_population
     */
    protected function setAvgPopulation($total_current_population)
    {
        $this->avg_population = ($this->master_month_tally * $this->avg_population + $total_current_population)
            / ($this->master_month_tally + 1);
    }

    /**
     * @param int $total_current_population
     * @return void
     */
    protected function setMidPointPopulation($total_current_population)
    {
        if ($this->month_tally === $this->month_midpoint) {
            $this->mid_point_population = $total_current_population;
        }
    }

    /**
     * @return float
     */
    protected function saveMortalityRate()
    {
        if ($this->mid_point_population === 0) {
            $this->mortality_rates[] = 100;
        } else {
            $this->mortality_rates[] = array_sum($this->death_counts) / $this->mid_point_population;
        }
    }

    /**
     * @return void
     */
    protected function replenish()
    {
        $this->food += $this->habitat->getMonthlyFood();
        $this->water += $this->habitat->getMonthlyWater();
    }

    /**
     * Breeds the animals
     *
     * @return void
     */
    protected function breed()
    {

        $is_viable_male = false;
        foreach ($this->males as $male) {
            if ($male->canBreed()) {
                $is_viable_male = true;
                break;
            }
        }

        if ($is_viable_male) {
            $is_sustainable_resources = $this->isSustainableResources();
            foreach ($this->females as $female) {
                if ($female->canBreed()) {
                    if ($is_sustainable_resources) {
                        $female->impregnate();
                    } elseif (rand(0, 199) === 0) {
                        $female->impregnate();
                    }
                }
            }
        }
    }

    protected function giveBirth()
    {
        foreach ($this->females as $female) {
            if ($female->canBirth()) {
                $baby = $female->birth();

                if ($baby->getSex() === Animal::SEX_FEMALE) {
                    $this->females->append($baby);
                } else {
                    $this->males->append($baby);
                }
            }
        }
    }

    /**
     * Randomly distributes water amongst animals
     *
     * @return void
     */
    protected function drink()
    {
        /** @var Animal[]|\ArrayObject $combined */
        $combined = new \ArrayObject();
        foreach ($this->males as $male) {
            $combined->append($male);
        }
        foreach ($this->females as $female) {
            $combined->append($female);
        }

        $combined->uasort(
            function () {
                return rand(-1, 1);
            }
        );

        foreach ($combined as $animal) {
            if ($this->water >= $animal->getSpecies()->getMonthlyWaterConsumption()) {
                $this->water -= $animal->getSpecies()->getMonthlyWaterConsumption();
                //First animal not to reach condition above will finish off the water,
                //but not enough to sustain them, considered a dehydration month
            } else {
                $this->water = 0;
                $animal->incrementDehydrationMonths();
            }
        }
    }

    /**
     * Randomly distributes food among animals
     *
     * @return void
     */
    protected function eat()
    {
        /** @var Animal[]|\ArrayObject $combined */
        $combined = new \ArrayObject();
        foreach ($this->males as $male) {
            $combined->append($male);
        }
        foreach ($this->females as $female) {
            $combined->append($female);
        }

        $combined->uasort(
            function () {
                return rand(-1, 1);
            }
        );

        foreach ($combined as $animal) {
            if ($this->food >= $animal->getSpecies()->getMonthlyFoodConsumption()) {
                $this->food -= $animal->getSpecies()->getMonthlyFoodConsumption();
                $animal->resetStarvationMonths();
            } else {
                //First animal not to reach condition above will finish off the food,
                //but not enough to sustain them, considered a starving month
                $this->food = 0;
                $animal->incrementStarvationMonths();
            }
        }
    }

    /**
     * Ages the animals, increments gestation period for females
     *
     * @return void
     */
    protected function aging()
    {
        foreach ($this->males as $male) {
            $male->incrementAgeByMonth();
        }

        foreach ($this->females as $female) {
            $female->incrementAgeByMonth();
        }
    }

    /**
     * Kills animals based on various criteria
     *
     * @return void
     */
    protected function death()
    {
        $dying_males = array();
        foreach ($this->males as $key => $male) {
            if ($male->isStarved()) {
                $dying_males[] = $key;
                $this->death_counts[self::DEATH_COUNT_STARVATION]++;
            } elseif ($male->isDehydrated()) {
                $dying_males[] = $key;
                $this->death_counts[self::DEATH_COUNT_THIRST]++;
            } elseif ($male->isHotTemp()) {
                $dying_males[] = $key;
                $this->death_counts[self::DEATH_COUNT_HOT_WEATHER]++;
            } elseif ($male->isColdTemp()) {
                $dying_males[] = $key;
                $this->death_counts[self::DEATH_COUNT_COLD_WEATHER]++;
            } elseif ($male->isOld()) {
                $dying_males[] = $key;
                $this->death_counts[self::DEATH_COUNT_AGE]++;
            }
        }

        foreach ($dying_males as $key) {
            $this->males->offsetUnset($key);
        }

        $dying_females = array();
        foreach ($this->females as $key => $female) {
            if ($female->isStarved()) {
                $dying_females[] = $key;
                $this->death_counts[self::DEATH_COUNT_STARVATION]++;
            } elseif ($female->isDehydrated()) {
                $dying_females[] = $key;
                $this->death_counts[self::DEATH_COUNT_THIRST]++;
            } elseif ($female->isHotTemp()) {
                $dying_females[] = $key;
                $this->death_counts[self::DEATH_COUNT_HOT_WEATHER]++;
            } elseif ($female->isColdTemp()) {
                $dying_females[] = $key;
                $this->death_counts[self::DEATH_COUNT_COLD_WEATHER]++;
            } elseif ($female->isOld()) {
                $dying_females[] = $key;
                $this->death_counts[self::DEATH_COUNT_AGE]++;
            }
        }

        foreach ($dying_females as $key) {
            $this->females->offsetUnset($key);
        }
    }

    /**
     * Calculates temperature and tallies hazardous temps
     *
     * @return void
     */
    protected function fryOrFreeze()
    {
        $current_temp = $this->calculateTemperature();

        if ($current_temp > $this->species->getMaximumTemperature()) {
            foreach ($this->males as $male) {
                $male->incrementHotTemperatureMonths();
            }
            foreach ($this->females as $female) {
                $female->incrementHotTemperatureMonths();
            }
        } elseif ($current_temp < $this->species->getMinimumTemperature()) {

            foreach ($this->males as $male) {
                $male->incrementColdTemperatureMonths();
            }
            foreach ($this->females as $female) {
                $female->incrementColdTemperatureMonths();
            }
        }
    }

    /**
     * Calculates temperature
     *
     * @return int
     */
    protected function calculateTemperature()
    {
        $avg_temp = $this->habitat->getAverageTemperature($this->current_month);
        if (rand(0, 199) === 0) {
            $half_range = 15;
        } else {
            $half_range = 5;
        }

        $low  = $avg_temp - $half_range;
        $high = $avg_temp + $half_range;

        return rand($low, $high);
    }

    /**
     * @return bool
     */
    protected function isSustainableResources()
    {
        $total_population = $this->males->count() + $this->females->count();

        return $total_population * $this->species->getMonthlyFoodConsumption() < $this->food
        && $total_population * $this->species->getMonthlyWaterConsumption() < $this->water;
    }

    /**
     * @return void
     */
    protected function updateCurrentMonth()
    {
        $this->current_month = $this->current_month === 12 ? 1 : $this->current_month + 1;
    }
}
