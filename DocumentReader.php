<?php

require_once 'vendor/autoload.php';
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Stichoza\GoogleTranslate\TranslateClient;

require_once "functions.php";

class DocumentReader
{
    private $detailsRu = null;
    private $clientRu = null;
    private $crawlerRu = null;
    private $detailsIt = null;
    private $clientIt = null;
    private $crawlerIt = null;
    private $link = null;
    private $translator = null;

    public function __construct($link)
    {
        $this->link = substr($link, strrpos($link, '/') + 1);

        $this->clientRu = new Client([
            'base_uri' => 'http://nedvizhimost-italii.immobiliare.it/'
        ]);
        $this->detailsRu = $this->clientRu->get($this->link);
        $this->detailsRu = $this->detailsRu->getBody()->getContents();
        $this->crawlerRu = new Crawler($this->detailsRu);

        $this->clientIt = new Client(
            [
                'base_uri' => 'http://www.immobiliare.it/'
            ]
        );
        $this->detailsIt = $this->clientIt->get($this->link);
        $this->detailsIt = $this->detailsIt->getBody()->getContents();
        $this->crawlerIt = new Crawler($this->detailsIt);
        $this->translator = new TranslateClient('it', 'ru');
    }

    public function getDetails()
    {
        return $this->detailsRu;
    }

    public function getTitle()
    {
        $titleElem = $this->crawlerRu->filter('div#sx  strong.h3')->text();
        $titleText = explode(' ', trim($titleElem))[0];

        $region = $this->crawlerIt->filter('h1.h4')->last()->text();
        return 'Объект № ' . $titleText . ' в ' . ucfirst($region);
    }

    public function getAttributes()
    {

        $price = $this->getPrice();
        $price = trim(ltrim($price, '€')) . ' €';

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

        $tables = $this->crawlerIt->filter('div.section-data > dl.col-xs-12');
        $tables = $tables->each(function (Crawler $node, $i) {
            return $node->children()->each(function (Crawler $val, $i) {
                return strip_tags($val->text());
            });
        });

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

        $charact = $this->crawlerIt->filter('div.section-data > div.col-xs-12 >span.label-gray');

        $charact = $charact->each(function (Crawler $text) {
            return $this->translator->translate($text->text());
        });

        $result = array('Цена' => isset($price) ? $price : '',
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

        foreach ($charact as $key => $value) {
            if (trim($value) != 'Двойная экспозиция') {
                if (trim($value) == 'тераццо') {
                    $char = "Терраса";
                    $result[$char] = 'Да';
                } else {
                    $char = mb_ucfirst($value);
                    $result[$char] = 'Да';
                }
            }
        }
//        $result['EMPTY'] = ' ';

        return $result;
    }

    public function getPrice()
    {
        $titleElem = $this->crawlerRu->filter('div#prezzoImmobile strong.h3')->text();
        $price = explode(':', $titleElem);
        return trim($price[1]);
    }

    public function getDescription()
    {
        $textIt = $this->crawlerIt->filter('div.description-text')->text();
        $textRu = $this->translator->translate($textIt);
        $textRu = trim(preg_replace('/\s+/', ' ', $textRu));
        return $textRu;
    }

    public function getImages()
    {
        $images = [];
        $imageBlock = $this->crawlerRu->filter('div#thumbs-mask');
        foreach ($imageBlock->children() as $div) {
            $crawlerDiv = new Crawler($div);
            $imgId = $crawlerDiv->filter('div.box_thumb')->attr('data-idimg');
            if ($imgId != '')
                $images[] = "http://pic.im-cdn.it/image/$imgId/print.jpg";
        }

        if ($this->crawlerIt->filter('div#planimetria')->count() > 0) {
            $planBlock = $this->crawlerIt->filter('div#planimetria > div.container-carousel > div.showcase > div.showcase__list');
            foreach ($planBlock->children() as $img) {
                $crawlerImg = new Crawler($img);
                $img = $crawlerImg->filter('img')->attr('src');
                if ($img != '')
                    $images[] = $img;
            }
        }
        return $images;
    }

    public function getMap()
    {
        $addressData = $this->crawlerIt->filter('div.maps-address > span > strong');
        $address = $addressData->text();

        $address = trim($address);

        $map[] = "https://maps.googleapis.com/maps/api/staticmap?center=" . getCoordinates($address)
            . "&markers=color:blue%7Clabel:O%7C" . getCoordinates($address) . "&zoom=11&size=650x300";
        $map[] = "https://maps.googleapis.com/maps/api/staticmap?center=" . getCoordinates($address)
            . "&zoom=15&size=650x300";

        return $map;
    }

}