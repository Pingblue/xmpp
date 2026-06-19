# xmpp
This is an updated version of the ruTorrent XMPP plugin that replaces the legacy XMPPHP library with a Python-based sender using Slixmpp.

The original ruTorrent XMPP plugin relies on the XMPPHP library, which is no longer maintained and is incompatible with modern PHP versions. On PHP 8.x systems the original plugin can fail with fatal errors, preventing notifications from being delivered.

This version retains the existing ruTorrent configuration interface while replacing the notification backend with a modern, actively maintained XMPP implementation.

Features
Compatible with modern PHP versions (PHP 8.x)
Supports STARTTLS on port 5222
Works with modern XMPP servers such as OpenIM and ejabberd
No Composer dependencies required
Preserves existing ruTorrent XMPP settings and user configuration
Simple deployment and maintenance
Requirements
Python 3
python3-slixmpp
python3-slixmpp-lib

Debian / Ubuntu:

apt update

apt install -y python3-slixmpp python3-slixmpp-lib
