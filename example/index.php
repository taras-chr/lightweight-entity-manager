<?php

use Doctrine\Common\Collections\ArrayCollection;
use EntityManager\Example\Mapper\CountryMapper;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @var \EntityManager\Example\Entity\Country[] $countries
 */
$countries = [];

$countryCodes = ['ua', 'us', 'de', 'nl', 'jp'];

foreach ($countryCodes as $countryCode) {
    $collection = new ArrayCollection(
        json_decode(file_get_contents("https://restcountries.eu/rest/v2/alpha/{$countryCode}"), true)
    );
    $country = new CountryMapper($collection);
    $countries[] = $country->getMapped();
}

dump($countries);
