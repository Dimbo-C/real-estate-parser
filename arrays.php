<?php

$arrays = [
        "requiredFields" => [
                "casa" => [
                        ["it" => "Locali", "ru" => "Комнаты", "en" => "Rooms"],
                        ["it" => "Bagni", "ru" => "Ванные", "en" => "Bathrooms"],
                        ["it" => "Condizioni", "ru" => "Состояние", "en" => "Condition"],
                        ["it" => "Piano", "ru" => "Этаж", "en" => "Floor"],
                        ["it" => "Posti Auto", "ru" => "Гараж", "en" => "Garage"],
                        ["it" => "Riscaldamento", "ru" => "Отопление", "en" => "Heating"],
                        ["it" => "Balcone", "ru" => "Балкон", "en" => "Balcony"],
                        ["it" => "Terrazzo", "ru" => "Терасса", "en" => "Terrace"]
                ]
        ],
        'sites' => ['casa', 'immobiliare', 'idealista'],
        'images' => [
                'casa_bottom' => "resources/images/overlay_footer_image.jpeg"
        ],
        'coordinates' => [
                'casa_bottom' => [10, 410, 0, 0, 100, 27]
        ]

];

function data($arrayName, $key1 = null, $key2 = null) {
    global $arrays;
    return $key1 == null
            ? $arrays[$arrayName]
            : $key2 == null
                    ? $arrays[$arrayName][$key1]
                    : $arrays[$arrayName][$key1][$key2];
}

function getSites() {
    global $arrays;
    return $arrays['sites'];
}

function getRequiredFields($sitename) {
    global $arrays;
    return $arrays['requiredFields'][$sitename];
}

