# Simple File Manager Api
> Simple File Manager is a PHP REST Api based file manager. It is a simple, fast and small size that can be dropped into any folder on your server. This can integrate into automation work where you need to upload, store/delete file versions. The Application runs on PHP 8.1+. It allows the creation of multiple users. The first version does not support separate folders or logical separation of files. 
> I have created this project mostly to support some automation work that I was doing so feel free to fork or clone and enhance this project. I in no way claim to say that this is perfect so please find issues and I will try my best to solve them.

### Requirements
- PHP 8.1+ (Not tested on lower versions)
- Composer (Dependencies are firebase/jwt and uuid)

### Installation Notes
1. ZIP Method
   1. Download the zip file from main branch
   2. Create folder on your server and unzip the file.
   3. This can be for apache/nginx etc.
2. Clone the main branch and the remaining steps are the same.
   
### Instructions
1. settings.json file contains all the settings error messages, informational messages, secret key etc. you can tweak it as you please.
2. administrator default password is "admin@123"
3. password is stored in settings.json file under users. [Bcrypt generator](https://bcrypt-generator.com/) i used to generate password like -> [sha256](https://emn178.github.io/online-tools/sha256.html) of ***admin@123*** -> ***7676aaafb027c825bd9abab78b234070e702752f625b752e55e55b48e607e358***

##### Request body for access token
- Create a POST request to token endpoint https://test.abc.com/token
- Content-Type: application/json
```json
{
    "username": "admin",
    "password": "7676aaafb027c825bd9abab78b234070e702752f625b752e55e55b48e607e358"
}
```

##### Access token response is 200 Ok
```json
{
    "access_token": "<TOKEN>"
}
```

##### Upload file
- use this token to upload file to desired storage location in settings.json
- example python code for automation can be something like below
```python
import requests

url = "https://test.abc.com/files"

payload={}
files=[
  ('file',('qp.png',open('/Users/test/Downloads/1.png','rb'),'image/png'))
]
headers = {
  'Authorization': 'Bearer <TOKEN>'
}

response = requests.request("POST", url, headers=headers, data=payload, files=files)

print(response.text)
```
- endpoint response 
```json
{
    "message": "file uploaded successfully"
}
```
##### Get all file list in the storage location
- call the files endpoint to get list of all files https://test.abc.com/files
```python
import requests
import json

url = "https://test.abc.com/files"

payload={}
headers = {
  'Content-Type': 'application/json',
  'Authorization': 'Bearer <TOKEN>'
}

response = requests.request("GET", url, headers=headers, data=payload)

print(response.text)

```
- endpoint response
```json
{
    "b7ed249a-08fc-4b93-840d-390f55afaaf9": {
        "file_hash": "75e29b5e25748ef2b49c9c696ebf40544dbac1ea",
        "file_name": "a2b1c620105474cec299b25e37a2e89bc5f9c104.pdf"
    },
    "e4c6fdea-355a-4fba-a982-c751ce0af20a": {
        "file_hash": "80fca6d4653175f312875cd56cfc88370aadce6d",
        "file_name": "184753d1626aa0ccdabfe833e3007958deaa4025.aab"
    }
}
```

##### Download file from the storage location
- call endpoint to download specific file with uuid in query string https://test.abc.com/files/uuid
```python
import requests
import json

url = "https://test.abc.com/files/b7ed249a-08fc-4b93-840d-390f55afaaf9"

payload={}
headers = {
  'Content-Type': 'application/json',
  'Authorization': 'Bearer <TOKEN>'
}

response = requests.request("GET", url, headers=headers, data=payload)

print(response.text)
```

##### Delete file from the storage location
- call endpoint to delete specific file with uuid in query string https://test.abc.com/files/uuid


#### License, Credit

- Available under [GNU Licence](https://github.com/prasathmani/tinyfilemanager/blob/master/LICENSE)
- Original concept and development - own.
- Dependencies used:
  - [Firebase/Jwt](https://github.com/firebase/)php-jwt
  - [Ramsey/uuid](https://github.com/ramsey/uuid)