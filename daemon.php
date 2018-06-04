<?php
require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

function arpScan()
{
    $interface = getenv('INTERFACE');
    $arpScanPath = getenv('ARP_SCAN_PATH');

    //https://gist.github.com/clarkwinkelmann/e0f67db7ac577d086c7d
    $scan = explode("\n", shell_exec("$arpScanPath --interface=$interface --localnet"));
    $devices = [];

    foreach ($scan as $line) {
        if (preg_match('/^([0-9\.]+)[[:space:]]+([0-9a-f:]+)[[:space:]]+(.+)$/', $line, $chunks) !== 1)
            continue;

        $devices[] = [
            'ip' => $chunks[1],
            'mac' => $chunks[2],
            'desc' => $chunks[3],
            'timestamp' => time()
        ];
    }
    return $devices;
}

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$serviceAccount = ServiceAccount::fromJsonFile(__DIR__ . '/google-service-account.json');
$firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->create();
$database = $firebase->getDatabase();

//PERFORM SCAN AND CREATE-UPDATE DEVICES
$devices = arpScan();

foreach ($devices as $device) {
    //CHECK IF DEVICE EXISTS
    $fbDevice = $database->getReference('devices')->orderByChild('mac')->equalTo($device['mac'])->getValue();
    if (count($fbDevice) > 0) {
        $key = array_keys($fbDevice)[0];
        $database->getReference('devices/' . $key . "/timestamp")->set($device['timestamp']);
        $database->getReference('devices/' . $key . "/ip")->set($device['ip']);

    } else {
        $newPost = $database
            ->getReference('devices')
            ->push($device);
    }
}

//DELETE TIMEOUT

if (getenv('DELETE_INACTIVE_DEVICES')) {
    $timeout = getenv('DELETE_INACTIVE_DEVICES_AFTER');
    $limit = new \DateTime("-$timeout");
    $expiredDevices = $database->getReference('devices')->orderByChild('timestamp')->endAt($limit->getTimestamp())->getValue();

    foreach ($expiredDevices as $key => $device) {
        $database->getReference('devices/' . $key)->remove();
    }
}





