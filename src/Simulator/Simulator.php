<?php
/**
 * @author Thomas Johnell <tjohnell@gmail.com>
 * @date   6/17/2015
 */

namespace SpeciesSim\Simulator;

/**
 * Class Simulator
 *
 * @package SpeciesSim
 */
class Simulator
{

    /**
     * @var int
     */
    protected $years;

    /**
     * @var int
     */
    protected $iterations;

    /**
     * @var \SpeciesSim\Species\Species[]
     */
    protected $species;

    /**
     * @var \SpeciesSim\Habitat\Habitat[]
     */
    protected $habitats;

    /**
     * @param int   $years
     * @param int   $iterations
     * @param array $species
     * @param array $habitats
     */
    public function __construct($years, $iterations, array $species, array $habitats)
    {
        $this->years      = $years;
        $this->iterations = $iterations;
        $this->species    = $species;
        $this->habitats   = $habitats;
    }

    public function run()
    {
        $report = array();
        foreach ($this->species as $species) {
            if (!array_key_exists($species->getName(), $report)) {
                $report[$species->getName()] = array();
            }
            foreach ($this->habitats as $habitat) {
                $report[$species->getName()][$habitat->getName()] = array();
                $world                                            = new World($habitat, $species, $this->years, 1);
                $iterations_report                                = $world->simulate($this->iterations);
                $report[$species->getName()][$habitat->getName()] = $iterations_report;
            }
        }

        return $report;
    }
}
