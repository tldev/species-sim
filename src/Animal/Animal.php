<?php
/**
 * @author Thomas Johnell <tjohnell@gmail.com>
 * @date   6/17/2015
 */

namespace SpeciesSim\Animal;

use SpeciesSim\Species\Species;

/**
 * Class Animal
 *
 * @package
 */
class Animal
{

    const SEX_MALE   = true;
    const SEX_FEMALE = false;

    const MAX_STARVATION_MONTHS  = 3;
    const MAX_DEHYDRATION_MONTHS = 1;
    const MAX_HOT_TEMP_MONTHS    = 1;
    const MAX_COLD_TEMP_MONTHS   = 1;

    /**
     * @var Species
     */
    protected $species;

    /**
     * @var int
     */
    protected $age_in_months = 0;

    /**
     * @var int
     */
    protected $starvation_months = 0;

    /**
     * @var int
     */
    protected $dehydrated_months = 0;

    /**
     * @var int
     */
    protected $hot_temp_months = 0;

    /**
     * @var int
     */
    protected $cold_temp_months = 0;

    /**
     * @var bool
     */
    protected $is_pregnant = false;

    /**
     * @var int
     */
    protected $gestation_months = 0;

    /**
     * @var bool
     */
    protected $sex;

    /**
     * @param Species $species
     * @param bool    $sex
     */
    public function __construct(Species $species, $sex)
    {
        $this->species = $species;
        $this->sex     = $sex;
    }

    /**
     * @return bool
     */
    public function canBreed()
    {
        return !$this->is_pregnant && $this->getAge() >= $this->species->getMinimumBreedingAge()
        && $this->getAge() <= $this->species->getMaximumBreedingAge();
    }

    public function canBirth()
    {
        return $this->is_pregnant && $this->gestation_months === $this->species->getGestationPeriod();
    }

    /**
     * @return void
     */
    public function impregnate()
    {
        if ($this->sex === self::SEX_MALE) {
            throw new \RuntimeException('You\'ve imgregnated a male. Game over.');
        }

        $this->is_pregnant = true;
    }

    /**
     * @return Animal
     */
    public function birth()
    {
        $sex    = rand(0, 1) === 0 ? self::SEX_MALE : self::SEX_FEMALE;
        $animal = new Animal($this->species, $sex);

        $this->is_pregnant      = false;
        $this->gestation_months = 0;

        return $animal;
    }

    /**
     * @return bool
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @return Species
     */
    public function getSpecies()
    {
        return $this->species;
    }

    /**
     * @return void
     */
    public function incrementDehydrationMonths()
    {
        $this->dehydrated_months++;
    }

    /**
     * @return void
     */
    public function incrementStarvationMonths()
    {
        $this->starvation_months++;
    }

    /**
     * @return void
     */
    public function resetStarvationMonths()
    {
        $this->starvation_months = 0;
    }

    /**
     * @return bool
     */
    public function isStarved()
    {
        return $this->starvation_months >= self::MAX_STARVATION_MONTHS;
    }

    /**
     * @return bool
     */
    public function isDehydrated()
    {
        return $this->dehydrated_months >= self::MAX_DEHYDRATION_MONTHS;
    }

    /**
     * @return bool
     */
    public function isOld()
    {
        return $this->getAge() >= $this->species->getLifeSpan();
    }

    /**
     * @return bool
     */
    public function isHotTemp()
    {
        return $this->hot_temp_months >= self::MAX_HOT_TEMP_MONTHS;
    }

    /**
     * @return bool
     */
    public function isColdTemp()
    {
        return $this->cold_temp_months >= self::MAX_COLD_TEMP_MONTHS;
    }

    /**
     * @return void
     */
    public function incrementHotTemperatureMonths()
    {
        $this->hot_temp_months++;
    }

    /**
     * @return void
     */
    public function incrementColdTemperatureMonths()
    {
        $this->cold_temp_months++;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return (int)($this->age_in_months / 12);
    }

    /**
     * @return void
     */
    public function incrementAgeByMonth()
    {
        $this->age_in_months++;

        if ($this->is_pregnant) {
            $this->gestation_months++;
        }
    }
}
