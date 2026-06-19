<?php

require_once(dirname(__FILE__) . "/../../php/settings.php");

class rXmpp
{
    public string $hash = "xmpp.dat";
    public bool $modified = false;
    public string $jabberHost = "";
    public int $jabberPort = 5222;
    public string $jabberLogin = "";
    public string $jabberServer = "";
    public string $jabberPasswd = "";
    public int $useEncryption = 1;
    public int $advancedSettings = 0;
    public string $jabberFor = "";
    protected string $message_templ = "Torrent '{TORRENT}' has been downloaded.";
    public string $message = "";

    public static function load(): self
    {
        $cache = new rCache();
        $at = new rXmpp();
        $cache->get($at);
        return $at;
    }

    public function store(): mixed
    {
        $cache = new rCache();
        return $cache->set($this);
    }

    public function set(): void
    {
        $rawPost = file_get_contents("php://input");

        if ($rawPost === false || $rawPost === '') {
            $this->store();
            return;
        }

        $vars = explode('&', $rawPost);

        $this->jabberHost = "";
        $this->jabberPort = 5222;
        $this->jabberLogin = "";
        $this->jabberServer = "";
        $this->jabberPasswd = "";
        $this->useEncryption = 1;
        $this->advancedSettings = 0;
        $this->jabberFor = "";
        $this->message = $this->message_templ;

        // Pre-initialise locals used conditionally below
        $jabberHost = "";
        $jabberPort = 0;
        $useEncryption = 1;

        foreach ($vars as $var) {
            $parts = explode("=", $var, 2);
            if (count($parts) < 2) {
                continue;
            }

            [$key, $value] = $parts;

            match ($key) {
                'jabberHost'       => $jabberHost = $value,
                'jabberPort'       => $jabberPort = (int) $value,
                'jabberPasswd'     => $this->jabberPasswd = $value,
                'useEncryption'    => $useEncryption = (int) $value,
                'advancedSettings' => $this->advancedSettings = (int) $value,
                'jabberFor'        => $this->jabberFor = $value,
                'jabberJid'        => (function () use ($value) {
                    $jid = explode("@", $value);
                    $this->jabberLogin = $jid[0];
                    $this->jabberServer = count($jid) > 1 ? $jid[1] : "";
                })(),
                'message' => (function () use ($value) {
                    if ($value !== '') {
                        $this->message = $value;
                    }
                })(),
                default => null,
            };
        }

        if ($this->advancedSettings) {
            if ($jabberHost !== '') {
                $this->jabberHost = $jabberHost;
            }
            if ($jabberPort > 0) {
                $this->jabberPort = $jabberPort;
            }
            $this->useEncryption = $useEncryption;
        }

        $this->setHandlers();
        $this->store();
    }

    public function get(): string
    {
        $jid = ($this->jabberLogin !== '' && $this->jabberServer !== '')
            ? $this->jabberLogin . '@' . $this->jabberServer
            : '';

        $message = addslashes($this->message !== '' ? $this->message : $this->message_templ);

        return "theWebUI.xmpp = { " .
            "JabberHost: '" . $this->jabberHost . "'" .
            ", JabberPort: " . $this->jabberPort .
            ", JabberJID: '" . $jid . "'" .
            ", JabberPasswd: '" . $this->jabberPasswd . "'" .
            ", UseEncryption: " . $this->useEncryption .
            ", AdvancedSettings: " . $this->advancedSettings .
            ", JabberFor: '" . $this->jabberFor . "'" .
            ", Message: '" . $message . "'" .
            " };\n";
    }

    public function setHandlers(): bool
    {
        $theSettings = rTorrentSettings::get();
        $pathToXmpp = dirname(__FILE__);
        $req = new rXMLRPCRequest();

        if (
            $this->message !== '' &&
            $this->jabberServer !== '' &&
            $this->jabberLogin !== '' &&
            $this->jabberPasswd !== '' &&
            $this->jabberFor !== ''
        ) {
            $cmd = $theSettings->getOnFinishedCommand([
                'xmpp' . User::getUser(),
                getCmd('execute.nothrow.bg') . '={' . Utility::getPHP() . ',' . $pathToXmpp . '/notify.php,"$' . getCmd('d.name') . '=","' . User::getUser() . '"}'
            ]);
        } else {
            $cmd = $theSettings->getOnFinishedCommand([
                'xmpp' . User::getUser(),
                getCmd('cat=')
            ]);
        }

        $req->addCommand($cmd);
        return $req->success();
    }
}
