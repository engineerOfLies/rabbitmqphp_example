# Download the helper library from https://www.twilio.com/docs/python/install
import os
from twilio.rest import Client
# Set environment variables for your credentials
# Read more at http://twil.io/secure
account_sid = "AC0968460f059bd60304455437a0a84958"
auth_token = "3c1d1d8306a1fcc3d13d87cbbcaf71d1"
client = Client(account_sid, auth_token)
message = client.messages.create(
  body="Stream East Message - New Episode of Breaking Bad: Face Off",
  from_="+18333061219",
  to="+17329968558"
)
print(message.sid)
