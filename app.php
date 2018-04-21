<?php

if (!file_exists('data/ALL_WSPR.TXT')) {
    die('File ALL_WSPR.TXT is missing');
}

$bandsWithFreqs = [
    [
        'band' => '2190m',
        'lower_freq' => 0.136,
        'upper_freq' => 0.137,
    ],
    [
        'band' => '20m',
        'lower_freq' => 14.0,
        'upper_freq' => 14.35,
    ],
];

$allWspr = file_get_contents('data/ALL_WSPR.TXT');

$stationCallSign = null;
$mode = 'FT8';
$txPower = 'ft897 30w';
$rstRcvd = -14;

$calls = [];

foreach (explode("\n", $allWspr) as $line) {
    if (trim($line) === "") {
        continue;
    }

    $lineWithoutExtraSpaces = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $line);
    $data = explode(" ", $lineWithoutExtraSpaces);

    if (strpos($line, 'Transmitting') !== false) {
        $stationCallSign = str_replace(['<', '>'], '', $data[5]);

        continue;
    }

    $band = null;
    $freq = $data[5];

    foreach ($bandsWithFreqs as $bandData) {
        if ($freq >= $bandData['lower_freq'] && $freq <= $bandData['upper_freq']) {
            $band = $bandData['band'];
        }
    }

    $calls[] = [
        'call' => $data[6],
        'gridsquare' => $data[7],
        'mode' => $mode,
        'rst_sent' => $data[3],
        'rst_rcvd' => $rstRcvd,
        'qso_date' => '20' . $data[0],
        'time_on' => $data[1],
        'band' => $band,
        'freq' => $freq,
        'station_callsign' => $stationCallSign,
        'tx_pwr' => $txPower,
    ];
}

$adifFile = <<<ADIF
ADIF Export<EOH>
ADIF;

foreach ($calls as $call) {
    $adifFile .= sprintf(
        "<CALL:5>%s <GRIDSQUARE:4>%s <MODE:3>%s <RST_SENT:3>%s <RST_RCVD:3>%s <QSO_DATE:8>%s <TIME_ON:6>%s <BAND:3>%s <FREQ:6>%s <STATION_CALLSIGN:5>%s <TX_PWR:9>%s <EOR>\n",
        $call['call'],
        $call['gridsquare'],
        $call['mode'],
        $call['rst_sent'],
        $call['rst_rcvd'],
        $call['qso_date'],
        $call['time_on'],
        $call['band'],
        $call['freq'],
        $call['station_callsign'],
        $call['tx_pwr']
    );
}

echo $adifFile;