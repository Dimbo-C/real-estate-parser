<?php

require_once __DIR__ . "/../../includes.php";
require_once __DIR__ . "/Parsable.php";

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Stichoza\GoogleTranslate\TranslateClient;

class ImmobiliareParser implements Parsable
{
    private $detailsRu;
    private $clientRu;
    private $crawlerRu;
    private $detailsIt;
    private $clientIt;
    private $crawlerIt;
    private $link;
    private $translator;
    private $resultingLang;

    public function __construct($link, $resultingLang = "ru")
    {
        //get everything after / in URL
        //        $this->link = substr($link, strrpos($link, '/') + 1);
        $this->link = explode(".it/", $link)[1];

        // guzzle client for ru version
        $this->clientRu = new Client([
            'base_uri' => 'http://nedvizhimost-italii.immobiliare.it/'
        ]);

        $this->detailsRu = $this->clientRu->get($this->link);
        $this->detailsRu = $this->detailsRu->getBody()->getContents();
        $this->crawlerRu = new Crawler($this->detailsRu);

        // guzzle client for it version
        $this->clientIt = new Client([
            'base_uri' => 'http://www.immobiliare.it/'
        ]);
        $this->detailsIt = $this->clientIt->get($this->link);
        $this->detailsIt = $this->detailsIt->getBody()->getContents();
        $this->crawlerIt = new Crawler($this->detailsIt);

        // instantiate translator
        $this->translator = new TranslateClient('it', 'ru');
        $this->resultingLang = $resultingLang;

//        dd($this->getTitleElem(), true);
    }

    public function getDetails()
    {
        return $this->detailsRu;
    }

    public function getTitle()
    {
        $title = $this->getTitleElem();
        $words = [
            "ru" => ["Объект №", "в"],
            "en" => ["Object №", "in"]
        ];

//        dd($title,true);
//        return $words[$this->resultingLang][0] . ' ' . trim($title['type']) . ' '
//            . $words[$this->resultingLang][1] . ' ' . ucfirst(trim($title['address']));
        return $words[$this->resultingLang][0] . ' ' . trim(' ') . ' '
            . $words[$this->resultingLang][1] . ' ' . ucfirst(trim($title['address']));
    }

    private function getTitleElem()
    {
        // get address from breadcrumbs
        $crumbs = $this->crawlerIt->filter('ol.breadcrumb');
        $main = $crumbs->children()->eq(0)->text();
        $inner = $crumbs->children()->eq(1)->text();
        $title['address'] = $main . ", " . $inner;

        // get all tables
        $tables = $this->crawlerIt->filter("div.section-data dl");
        $tables = $tables->each(function (Crawler $node) {
            return $node->children()->each(function (Crawler $val) {
                return trim(strip_tags($val->text()));
            });
        });

        // merge tables together
        $singleArray = [];
        foreach ($tables as $table) {
            $singleArray = array_merge($singleArray, $table);
        }

        // convert tables to key-value storage
        $keyValArray = [];
        for ($i = 0; $i < count($singleArray); $i += 2) {
            $keyValArray[$singleArray[$i]] = $singleArray[$i + 1];
        }

        // if there is no explicit typology of building - find it somewhere else
        if (array_key_exists("Tipologia", $keyValArray)) {
            $title['type'] = $keyValArray['Tipologia'];
        } else {
            $tipology = $this->crawlerIt->filter('div#tipologia tbody')->text();
            $title['type'] = $tipology;
        }


        return $title;
    }

    // get attributes for table with attributes
    public function getAttributes()
    {
        // fetch price
        $price = $this->getPrice();
        $price = trim(ltrim($price, '€')) . ' €';

        // and others
        $sqare = $this->crawlerIt->filter('div.feature-action__features > ul.list-inline > li > div > strong')->last();
        $sqare = $sqare->count() > 0 ? $sqare->text() : '';

        $room = $this->crawlerIt->filter('div.feature-action__features > ul.list-inline > li > div > i.rooms');
        $room = $room->count() > 0 ? $room->previousAll()->filter('strong')->text() : '';

        $bath = $this->crawlerIt->filter('div.feature-action__features > ul.list-inline > li > div > i.bathrooms');
        $bath = $bath->count() > 0 ? $bath->previousAll()->filter('strong')->text() : '';

        $month_costs = $this->crawlerIt->filter('div.section-data > dl.col-xs-12 > dt.col-sm-7');
        if ($month_costs->count() > 0 && $month_costs->text() == 'Spese condominio') {
            $costs = $month_costs->nextAll()->text();
        }

        // get all tables on page (there can be more than one)
        $tables = $this->crawlerIt->filter('div.section-data > dl.col-xs-12');
        $tables = $tables->each(function (Crawler $node, $i) {
            return $node->children()->each(function (Crawler $val, $i) {
                return strip_tags($val->text());
            });
        });

        // translate specific table keys
        foreach ($tables as $table) {
            foreach ($table as $i => $val) {
                if ($val == 'Piano')
                    $floor = $this->translator->translate($table[$i + 1]);
                if ($val == 'Anno di costruzione')
                    $year = $this->translator->translate($table[$i + 1]);
                if ($val == 'Riscaldamento')
                    $warm = $this->translator->translate($table[$i + 1]);
                if ($val == 'Stato')
                    $state = $this->translator->translate($table[$i + 1]);
                //                if ($val == 'Climatizzatore')
                //                    $condi = $this->translator->translate($table[$i + 1]);
                if ($val == 'Box e posti auto')
                    $garage = $this->translator->translate($table[$i + 1]);
            }
        }

        // get all characteristics from page that should be marked as "yes" (да) and translate them
        $charact = $this->crawlerIt->filter('div.section-data > div.col-xs-12 >span.label-gray');
        $charact = $charact->each(function (Crawler $text) {
            return $this->translator->translate($text->text());
        });

        // array for resulting table
        $result = array(
            'Цена' => isset($price) ? $price : '',
            'Площадь' => isset($sqare) ? $sqare . 'кв.м' : '',
            'Комнаты' => isset($room) ? $room : '',
            'Ванные' => isset($bath) ? $bath : '',
            'Состояние' => isset($state) ? $state : '',
            'Этаж' => isset($floor) ? $floor : '',
            'Гараж' => isset($garage) ? $garage : '',
            'Вид на воду' => '',
            'Отопление' => isset($warm) ? $warm : '',
            //            'Кондиционер' => isset($condi)? $condi : '',
            'Год постройки' => isset($year) ? $year : '',
            'Жилищные расходы' => isset($costs) ? $costs : ''
        );

        // loop through characteristics and mark them as "yes" (да). And fix some incorrect translations
        foreach ($charact as $key => $value) {
            if (trim($value) != 'Двойная экспозиция') {
                $isTerazzo = trim($value) == 'тераццо';

                $char = $isTerazzo ? "Терасса" : mb_ucfirst($value);
                $result[$char] = 'Да';
            }
        }

        return $result;
    }

    // get price from russian site
    public function getPrice()
    {
//        $titleElem = $this->crawlerRu->filter('div#prezzoImmobile strong.h3')->text();
//        $price = explode(':', $titleElem);
//
//        return trim($price[1]);
        $price = $this->crawlerIt->filter('li.features__price > span')->text();
        return trim($price);
    }

    // fetch IT description and translate it to ru
    public function getDescription()
    {
        $textIt = $this->crawlerIt->filter('div.description-text')->text();
        $textRu = $this->translator->translate($textIt);
        $textRu = trim(preg_replace('/\s+/', ' ', $textRu));

        return $textRu;
    }

    // get images from rus
    public function getImages()
    {
        $media_json = json_decode($this->crawlerIt->filter('script#js-hydration')->text());
        $images = array();
        foreach ($media_json->multimedia->immagini->list as $image) {
            $images[] = $image->srcSet->large;
        }
//        dd($images, true);
        return $images;
    }


    public function getMap()
    {
        // get map address
        $address = $this->crawlerIt->filter('div.maps-address > span > strong')->text();
        $address = trim($address);

        // get images if maps from google maps
        // with marker, less zoomed
        $map[] = "https://maps.googleapis.com/maps/api/staticmap?center="
            . getCoordinates($address) . "&markers=color:blue%7Clabel:O%7C"
            . getCoordinates($address) . "&zoom=11&size=650x300";
        // without marker, more zoomed
        $map[] = "https://maps.googleapis.com/maps/api/staticmap?center="
            . getCoordinates($address) . "&zoom=15&size=650x300";

        return $map;
    }

}