<?php

if (!empty($_POST)) {
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

    $allWspr = file_get_contents($_FILES['wspr_file']['tmp_name']);

    $stationCallSign = null;
    $mode = $_POST['mode'];
    $txPower = $_POST['tx_power'];
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
            'call' => str_replace(['<', '>'], '', $data[6]),
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

    $files = [];
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


    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=result.adi');
    echo $adifFile;
    exit();
}


?>
<html>
<head>
    <title>WSPR to ADIF</title>
</head>
<body>
    <h1>WSPR to ADIF</h1>

    <form method="POST" enctype="multipart/form-data">
        <p>
            <label for="wspr_file">File *</label>
            <input type="file" id="wspr_file" name="wspr_file" required="required" />
        </p>

        <p>
            <label for="mode">Mode *</label>
            <input type="text" id="mode" name="mode" value="FT8" required="required" />
        </p>

        <p>
            <label for="tx_power">TX Power *</label>
            <input type="text" id="tx_power" name="tx_power" value="ft897 30w" required="required" />
        </p>


        <input type="submit" value="Process" />
    </form>

    <p>Source code is available here : <a href="https://github.com/notFloran/wspr_to_adif">https://github.com/notFloran/wspr_to_adif</a></p>
</body>
</html>
