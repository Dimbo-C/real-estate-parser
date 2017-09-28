<pre>
<?php
require_once 'vendor/autoload.php';
require_once 'DocumentReader.php';
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Stichoza\GoogleTranslate\TranslateClient;

$document = new DocumentReader('https://www.immobiliare.it/62028002-Vendita-Appartamento-via-Gluck-15-Milano.html');
$planBlock = $document->getImages()->children();
print_r($planBlock);
$planBlock->each(function (Crawler $val) {
    print_r($val);
});

