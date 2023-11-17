# Text Drop - Send arbitrary JSON data via HTTP

I had a need to send small chunks of JSON data between servers in different parts of the world.  Because of secuirty policies, inbound connections were disallowed, so it was required to reach out to an endpoint to retreive the data.

Like with a Dead Drop, one server can POST it's JSON data to an HTTP endpoint, while another server may pick it up at the known location.  Data is valid for a configurable amount of time, after which it will be ignored.

## Installation
Installation is very simple - simply install php and your favorite webserver, copy the php file to your webserver's root directory and create a temporary directory where the POSTed data will be saved.  Configure the full path to the temporary directory by setting $filePath.

You can optionally also change $maxLength to alter the amount of time data will be retained for (in seconds).  By default it is 1 day (86400 seconds).

## Usage
From a client's perspective, usage is very simple.  Data can be sent and retreived using curl.

### Sending Data

Data can be sent to the endpoint with the following command: `curl -s --fail -X POST https://drop.ip4.is -d 'payload={"id": "'$ID'","key":"'$KEY'","message":'$MSSAGE' }'`

wherein $ID is the identifier you will use to retrieve the dropped text, $KEY is your secret key used to retreive the text and $MESSAGE is the message you wish to send (it can be any valid JSON data).  it is important to note that you, as the user specify the id and the key.  If an id does not already exist, it will be created with the POSTed key, if the id already exists, the POSTed key will be checked aginst the existing ID on disk, if it matches the data will be updated, otherwise no action will occur and an error will be displyed to the user.

ID's must only be characters a-z, A-Z or 0-9.  The key must be a minium of 5 characters.

If sucessful, you should receivie the following JSON response: `{"success": 1, "data":{"message": "sucessfully saved POST ID $ID","post_id":"$ID"}}`

If there is an error, then you will receivie a response describing the problem, for instance if the key you are submitting does not match the ID: `{"success": 0, "data":{"message": "key does not match old record"}}`

### Retreiving Data

Once data has been sent to the endpoint, it can be retrievied if both the id and the key are known.  Simply send a GET request to the server and provide the key and id as parameters: `curl "https://drop.ip4.is?key=$KEY&id=$ID"`

If the id exists on the server, and the provided key matches it will be returned as a JSON object:
```
{
  "success": 1,
  "data": {
    "payload": {
      "message": "my awesome message"
    },
    "post_id": "$ID"
  }
}
```

If the request is not sucessful, an error will be returned with a decscription of the problem.  For instance, if the provided key didn't match: `{ "success": 0, "data": { "message": "Key does not match" }}`
