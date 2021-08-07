import hashlib
import random
import requests
import socket
import struct
import time

# script to generate a random IP address and feed data.

endpoint = 'http://127.0.0.1/api/v1/CreateEvent'
tokenid = '1393023732813824'
token = 'EA630C71-9E5C-4C4D-B1D0-8DCA5909465D'


def sendRandBasicInfo():
    ip = socket.inet_ntoa(struct.pack('>I', random.randint(1, 0xffffffff)))
    data = {'ip': ip, 'src_port': randPort(), 'dest_port': randPort(), 'report_type': 'TCP SYN Test (Fake Data)',
            'info': 'Fake Data'}
    cTime = int(time.time())
    expires = cTime + 60
    # /api/v1/CreateEvent?id=&time=
    params = {'id': tokenid, 'time': time, 'expires': expires, 'signature': getSig(time, expires)}
    print("ip: " + ip)
    print("Sending request")
    res = requests.post(endpoint, params=params, data=data)
    print("Got response: " + str(res.status_code))
    print(str(res.content, "utf-8"))


def getSig(cTime, eTime):
    return hashlib.sha256(
        (str(tokenid) + "|" + str(token) + "|" + str(cTime) + "|" + str(eTime)).encode('utf-8')).hexdigest()


def randPort():
    return random.randint(1, 65535)


while True:
    sendRandBasicInfo()
    time.sleep(1)
