import sys
import asyncio
import warnings
import slixmpp

jid = sys.argv[1]
password = sys.argv[2]
recipient = sys.argv[3]
message = sys.argv[4]
host = sys.argv[5] if len(sys.argv) > 5 else "openim.nl"
port = int(sys.argv[6]) if len(sys.argv) > 6 else 5222

class Bot(slixmpp.ClientXMPP):
    def __init__(self):
        super().__init__(jid, password)
        self.add_event_handler("session_start", self.start)

    async def start(self, event):
        self.send_presence()
        self.send_message(mto=recipient, mbody=message, mtype="chat")
        await asyncio.sleep(2)
        self.disconnect(wait=True)

xmpp = Bot()
xmpp.connect((host, port))
xmpp.process(forever=False)
