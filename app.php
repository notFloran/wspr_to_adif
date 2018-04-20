<?php

if (!file_exists('ALL_WSPR.TXT')) {
    die('File ALL_WSPR.TXT is missing');
}

$allWspr = file_get_contents('ALL_WSPR.TXT');

$stationCallSign = null;
$mode = 'FT8';

$calls = [];

foreach (explode("\n", $allWspr) as $line) {
    // @TODO get station call sign
    $stationCallSign = "F6IEO";

    if (strpos($line, 'Transmitting') !== false) {
        continue;
    }

    var_dump($line);
    $lineWithoutExtraSpaces = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $line);
    $data = explode(" ", $lineWithoutExtraSpaces);
    var_dump($data);

    $calls[] = [
        'Call' => $data[6],
        'gridsquare' => $data[7],
        'mode' => $mode,
        'rst_sent' => null,
        'rst_rcvd' => null,
        'qso_date' => null,
        'time_on' => null,
        'band' => null,
        'freq' => null,
        'station_callsign' => null,
        'tx_pwr' => null,
    ];
}

var_dump($calls);