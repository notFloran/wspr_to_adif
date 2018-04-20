<?php

if (!file_exists('data/ALL_WSPR.TXT')) {
    die('File ALL_WSPR.TXT is missing');
}

$allWspr = file_get_contents('data/ALL_WSPR.TXT');

$stationCallSign = null;
$mode = 'FT8';

$calls = [];

foreach (explode("\n", $allWspr) as $line) {
    if (trim($line) === "") {
        continue;
    }

    // @TODO get station call sign
    $stationCallSign = "F6IEO";

    if (strpos($line, 'Transmitting') !== false) {
        continue;
    }

    $lineWithoutExtraSpaces = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $line);
    $data = explode(" ", $lineWithoutExtraSpaces);

    $calls[] = [
        'call' => $data[6],
        'gridsquare' => $data[7],
        'mode' => $mode,
        'rst_sent' => $data[3],
        'rst_rcvd' => null,
        'qso_date' => '20' . $data[0],
        'time_on' => null,
        'band' => null,
        'freq' => $data[5],
        'station_callsign' => null,
        'tx_pwr' => null,
    ];
}

$adifFile = <<<ADIF
<ADIF_VER:5>3.0.8
<PROGRAMID:10>ADIFMaster
<PROGRAMVERSION:3>2.7
<EOH>

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