<?php
if (!chdir(dirname(__FILE__))) exit(1);

if (count($argv) > 2) {
    $_SERVER['REMOTE_USER'] = $argv[2];
}

require_once(dirname(__FILE__)."/../../php/settings.php");
require_once("xmpp.php");

$name = $argv[1] ?? 'ruTorrent notification';
$at = rXmpp::load();

if ($at->message !== '' && isset($at->jabberLogin, $at->jabberPasswd, $at->jabberFor)) {
    $server = $at->jabberServer ?? 'openim.nl';
    $host = !empty($at->jabberHost) ? $at->jabberHost : $server;
    $port = !empty($at->jabberPort) ? (int)$at->jabberPort : 5222;

    $jid = $at->jabberLogin;
    if (strpos($jid, '@') === false) {
        $jid .= '@' . $server;
    }

    $message = str_replace("{TORRENT}", $name, $at->message);

    $cmd = 'python3 ' . escapeshellarg(__DIR__ . '/send_xmpp.py') . ' '
    . escapeshellarg($jid) . ' '
    . escapeshellarg($at->jabberPasswd) . ' '
    . escapeshellarg($at->jabberFor) . ' '
    . escapeshellarg($message) . ' '
    . escapeshellarg($host) . ' '
    . escapeshellarg((string)$port)
    . ' 2>/dev/null';

exec($cmd, $output, $returnCode);

    if ($returnCode !== 0) {
        echo implode("\n", $output) . "\n";
        exit($returnCode);
    }
}
