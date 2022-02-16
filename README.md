# paystar

## Making transaction using finnotech api

## Available apis:

### api/account/register
- type: POST

- required fields in body:
    * card_number
    * password
    * password_confirmation

- required fields in header:
    * Accept: application/json
    * Authorization: Bearer {token}


### api/account/show
- type: GET

- required fields in body:
    * empty body

- required fields in header:
    * Accept: application/json
    * Authorization: Bearer {token}


### api/account/transactions
- type: GET

- required fields in body:
    * empty body

- required fields in header:
    * Accept: application/json
    * Authorization: Bearer {token}


### api/account/transfer
- type: POST

- required fields in body:
    * secondPassword
    * destinationNumber
    * amount
    * deposit

- required fields in header:
    * Accept: application/json
    * Authorization: Bearer {token}


### api/account/update
- type: PUT

- required fields in body:
    * card_number

- required fields in header:
    * Accept: application/json
    * Authorization: Bearer {token}


### api/login
- type: POST

- required fields in body:
    * email
    * password

- required fields in header:
    * Accept: application/json


### api/register
- type: POST

- required fields in body:
    * firstname
    * lastname
    * email
    * password
    * password_confirmation

- required fields in header:
    * Accept: application/json
