<?php

namespace HylaPlugins;
use Hyla\Config\Conf;

/**
 * Class GeoLoc
 *
 * usage :
 * echo "Test Geo Ip <br><br>";
 * echo "IP: ".$_SERVER['REMOTE_ADDR']."<br>";
 * //Afficher l'adresse ip du visiteur
 *
 * echo "GEOIP_AREA_CODE: ".$_SERVER['GEOIP_AREA_CODE']."<br>";
 * echo "GEOIP_CITY: ".$_SERVER['GEOIP_CITY']."<br>";
 * //Affiche La ville du visiteur
 *
 * echo "GEOIP_COUNTRY_CODE: ".$_SERVER['GEOIP_COUNTRY_CODE']."<br>";
 * //Affiche le code pays du visiteur (fr,en,be par exemple)
 *
 * echo "GEOIP_COUNTRY_NAME: ".$_SERVER['GEOIP_COUNTRY_NAME']."<br>";
 * //Affiche le pays du visiteur
 *
 * echo "GEOIP_DMA_CODE: ".$_SERVER['GEOIP_DMA_CODE']."<br>";
 * echo "GEOIP_LATITUDE: ".$_SERVER['GEOIP_LATITUDE']."<br>";
 * //Affiche la latitude
 *
 * echo "GEOIP_LONGITUDE: ".$_SERVER['GEOIP_LONGITUDE']."<br>";
 * //Affiche la longtitude
 *
 * echo "GEOIP_REGION: ".$_SERVER['GEOIP_REGION']."<br>";
 *
 * @package HylaPlugins
 */
class GeoLoc extends AbstractPlugins {

    public static function init(array $data = null) {
        $pathToGeoloc = __DIR__ . '/../lib/GeoIp/';
        if (empty($_SERVER['GEOIP_COUNTRY_CODE'])) {
            include($pathToGeoloc . 'geoipcity.inc');
            include($pathToGeoloc . 'geoipregionvars.php');
            $giCity = geoip_open($pathToGeoloc . 'GeoLiteCity.dat', GEOIP_STANDARD);

            $ip = $_SERVER['SERVER_ADDR'];
            $pattern = '/^192\.168/';
            if (preg_match($pattern, $ip) == 1) {
                $ip = Conf::get('environment.ip_local');
            }

            $record = geoip_record_by_addr($giCity, $ip);

            $_SERVER['GEOIP_COUNTRY_CODE'] = $record->country_code;
            $_SERVER['GEOIP_COUNTRY_NAME'] = $record->country_name;
            $_SERVER['GEOIP_REGION'] = $record->region;
            $_SERVER['GEOIP_CITY'] = $record->city;
            $_SERVER['GEOIP_DMA_CODE'] = $record->dma_code === null ? 0 : $record->dma_code;
            $_SERVER['GEOIP_AREA_CODE'] = $record->area_code === null ? 0 : $record->area_code;
            $_SERVER['GEOIP_LATITUDE'] = $record->latitude;
            $_SERVER['GEOIP_LONGITUDE'] = $record->longitude;
        }
    }

    protected static function display()
    {

    }


    protected static function execute()
    {
        return null;
    }
}
