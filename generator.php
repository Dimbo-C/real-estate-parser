<?php
require_once __DIR__ . "/includes.php";

// prev page in case of wrong url passed
$previousPage = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . "/parser/index.php";

if (isset($_POST['url'])) {
    $url = $_POST['url'];

    // test
//    $url = "http://www.casa.it/immobile-appartamento-lazio-roma-31433273";
//    $url = "http://www.casa.it/immobile-appartamento-lazio-roma-31433273";
//    $url = "https://www.immobiliare.it/nuove_costruzioni/Milano/119207-immobile.html";
//    $url = "https://www.immobiliare.it/61518026-Vendita-Appartamento-via-XXV-Aprile-4-Agugliano.html";

    $resultLang = isset($_POST['en']) ? "en" : "ru";
    $documentData = ["url" => $url, "lang" => $resultLang];

    // redirect if retarded url
    //        if ($url == '' || !getSiteFromUrl($url)) {
    //            // redirect if none of the sites are in the url
    //            header("Location: " . $previousPage);
    //        }

    // get data from url
    $document = ParseFactory::getSiteParser($documentData);


    // write found data to document
    $docWord = new DocumentWriter();
    $docWord->titleAndTable($document->getTitle(), $document->getAttributes(), $document->getPrice());
    $docWord->map($document->getMap());
    $docWord->description($document->getDescription());
    $docWord->images($document->getImages());
    $docWord->regards();
    $docWord->save();



    $newDoc = 'file.docx';

    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename=document.docx;');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($newDoc));

    readfile($newDoc);

    session_write_close();
} else {
    header("Location: " . $previousPage);
}