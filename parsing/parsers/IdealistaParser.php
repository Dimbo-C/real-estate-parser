<?php

require_once __DIR__ . "/../../includes.php";
require_once __DIR__ . "/Parsable.php";

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Stichoza\GoogleTranslate\TranslateClient;

class IdealistaParser implements Parsable {
    private $detailsRu = null;
    private $detailsIt = null;
    private $clientIt = null;
    private $crawlerIt = null;
    private $link = null;
    private $translator = null;
    private $resultingLang = null;

    public function __construct($link, $resultingLang = "ru") {
        $this->resultingLang = $resultingLang;
        $this->link = substr($link, strrpos($link, '/') + 1);

        $this->clientIt = new Client([
                'base_uri' => 'https://www.idealista.it/'
        ]);
        $this->detailsIt = $this->clientIt->get($this->link);
        $this->detailsIt = $this->detailsIt->getBody()->getContents();
        $this->crawlerIt = new Crawler($this->detailsIt);
        $this->translator = new TranslateClient('it', $resultingLang);

        // test
        //        $this->dd($this->getImages());
    }

    public function getDetails() {
        return $this->detailsRu;
    }

    public function getTitle() {
        $title = $this->getTitleElem();
        $words = ["ru" => ["Объект №", "в"], "en" => ["Object №", "in"]];

        return $words[$this->resultingLang][0] . ' ' . trim($title['type']) . ' '
                . $words[$this->resultingLang][1] . ' ' . ucfirst(trim($title['address']));
    }

    public function getArea() {
        $postfixes = ["ru" => "кв.м", "en" => "sq.ft"];
        $squareMeters = $this->crawlerIt->filter("span.mtq")->text()
                . " " . $postfixes[$this->resultingLang];

        return $squareMeters;
    }

    public function getAttributes() {
        // characteristics that required in resulting table
        $characteristics = $this->crawlerIt->filter("div.characteristics .general ul");
        $words = ["ru" => ["Цена", "Площадь"], "en" => ["Price", "Area"]];

        // init table with data that are already known
        $table = [
                $words[$this->resultingLang][0] => $this->getPrice(),
                $words[$this->resultingLang][1] => $this->getArea()
        ];

        // array of characteristic value (it,ru,en)
        $requiredFields = getRequiredFields("casa");

        // loop through table with characteristics
        foreach ($characteristics->children() as $characteristic) {
            // field name and value in it from node
            $data = explode(":", $characteristic->nodeValue);
            $key = $data[0];
            $value = $data[1];

            // put scrapped data in comfy resulting table
            // translate all non-numeric values to dest language
            foreach ($requiredFields as $field) {
                if ($key == $field["it"]) {
                    $value = is_numeric($value)
                            ? $value
                            : $this->translateChain(['it', 'en', 'ru'], $value);
                    $table[$field["ru"]] = $value;
                }
            }
        }

        return $table;
    }

    public function getPrice() {
        $priceHtml = $this->crawlerIt->filter('div.price-meters .price')->text();
        $price = str_replace("\"", "", $priceHtml);
        $price = trim(ltrim($price, '€')) . ' €';

        return $price;
    }

    public function getDescription() {
        $textIt = $this->crawlerIt->filterXPath('//meta[@name="description"]')->extract(array('content'))[0];
        $textRu = $this->translator->translate($textIt);
        $textRu = trim(preg_replace('/\s{2,}/', ' ', $textRu));

        return $textRu;
    }

    public function getImages() {
        // get all script tags on page init
        $scripts = $this->crawlerIt->filter('script')->each(function (Crawler $node, $i) {
            return $node->text();
        });

        // get script with required data and parse it for picture info
        $scriptSignature = "window.__INITIAL_STATE__";
        $scriptRegex = '/\"media\":(\[.+?\])/';
        foreach ($scripts as $script) {
            if (strpos($script, $scriptSignature) !== false) {
                preg_match($scriptRegex, $script, $picsData);
                break;
            }
        }
        $picsData = json_decode($picsData[1]);

        // generate picture links and store them
        $images = [];
        $resolution = "655x483";
        foreach ($picsData as $data) {
            $images[] = "{$data->server}/{$resolution}{$data->uri}";
        }

        return $images;
    }

    public function getMap() {

        //        $addressData = $this->crawlerIt->filter('div.maps-address > span > strong');
        $address = $this->getTitleElem()['address'];

        $map[] = "https://maps.googleapis.com/maps/api/staticmap?center="
                . getCoordinates($address) . "&markers=color:blue%7Clabel:O%7C"
                . getCoordinates($address) . "&zoom=15&size=650x300";
        $map[] = "https://maps.googleapis.com/maps/api/staticmap?center="
                . getCoordinates($address) . "&zoom=15&size=650x300";

        return $map;
    }

    //////////////////////////////////////// PRIVATEES ///////////////////////////////////////////////////////////

    /**
     * Get title string and split it into 2 parts: type and address
     *
     * @return array that contains type of estate and address
     */
    private function getTitleElem() {
        $titleElem = trim($this->crawlerIt->filter('div.title-zone h1')->text());
        $title = preg_split('/in vendita/i', $titleElem);
        if (count($title) == 0) {
            $title = preg_split(' ', $titleElem);
        };
        $title['type'] = $title[0];
        $title['address'] = $title[1];

        return $title;
    }

    /**
     * @param array $chain array of languages that text will be translated to
     * @param $text string that will be translated
     * @return string text after multiple translations
     */
    private function translateChain(array $chain, $text) {
        for ($i = 0; $i < count($chain) - 1; $i++) {
            $text = $this->translator->setSource($chain[$i])->setTarget($chain[$i + 1])->translate($text);
        }
        $this->translator->setSource("it")->setTarget("ru");

        return $text;
    }

    private function dd($data, $isDie = false) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if ($isDie) die;
    }
}