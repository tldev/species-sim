<?php

use SpeciesSim\Habitat\Habitat;
use SpeciesSim\Simulator\Simulator;
use SpeciesSim\Species\Species;

require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('Must pass yaml file as first argument.');
}

$yaml_file = $argv[1];
if (!file_exists($yaml_file)) {
    throw new \InvalidArgumentException(sprintf('yaml file "%s" does not exist', $yaml_file));
}

$config     = SPYC::YAMLLoad($yaml_file);
$years      = $config['years'];
$iterations = $config['iterations'];
$species    = array();
foreach ($config['species'] as $species_current) {
    $attributes = $species_current['attributes'];
    $species[]  = new Species(
        $species_current['name'],
        $attributes['monthly_food_consumption'],
        $attributes['monthly_water_consumption'],
        $attributes['life_span'],
        $attributes['minimum_breeding_age'],
        $attributes['maximum_breeding_age'],
        $attributes['gestation_period'],
        $attributes['minimum_temperature'],
        $attributes['maximum_temperature']
    );
}
$habitats = array();
foreach ($config['habitats'] as $habitat) {
    $avg_temp   = $habitat['average_temperature'];
    $habitats[] = new Habitat(
        $habitat['name'],
        $habitat['monthly_food'],
        $habitat['monthly_water'],
        $avg_temp['summer'],
        $avg_temp['spring'],
        $avg_temp['fall'],
        $avg_temp['winter']
    );
}

$simulate = new Simulator($years, $iterations, $species, $habitats);
$report   = $simulate->run();

echo "Simulation ran for {$iterations} iterations at {$years} years per iteration" . PHP_EOL;
foreach ($report as $species => $habitats) {
    echo "$species:" . PHP_EOL;
    foreach ($habitats as $habitat => $data) {
        echo "        {$habitat}:" . PHP_EOL;
        echo "            Average Population: {$data['avg_pop']}" . PHP_EOL;
        echo "            Max Population: {$data['max_pop']}" . PHP_EOL;
        echo "            Mortality Rate: {$data['mortality_rate']}%" . PHP_EOL;
        echo "            Cause of Death:" . PHP_EOL;
        foreach($data['death_percentages'] as $name => $percentage) {
            echo str_pad($percentage, 26, " ", STR_PAD_LEFT) . "% {$name}" . PHP_EOL;
        }
    }
}
