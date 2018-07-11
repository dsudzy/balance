var bodyParser = require('body-parser');
var express = require('express');
var plaid = require('plaid');
console.log('erererer');
// We store the access_token in memory - in production, store it in a secure
// persistent data store
var ACCESS_TOKEN = null;
var PUBLIC_TOKEN = null;

var client = new plaid.Client(
  "5b28fcfe5666c40012691852",
  "6c399b9075ac92c43963497f7752a4",
  "3fd48ca30df117a9ed536aa76bfb50",
  plaid.environments.sandbox
);

// Accept the public_token sent from Link
var app = express();

app.post('/get_access_token', function(request, response, next) {
    console.log('errerrrrr');
  PUBLIC_TOKEN = request.body.public_token;
  client.exchangePublicToken(PUBLIC_TOKEN, function(error, tokenResponse) {
    if (error != null) {
      console.log('Could not exchange public_token!' + '\n' + error);
      return response.json({error: msg});
    }
    ACCESS_TOKEN = tokenResponse.access_token;
    ITEM_ID = tokenResponse.item_id;
    console.log('Access Token: ' + ACCESS_TOKEN);
    console.log('Item ID: ' + ITEM_ID);
    response.json({'error': false});
  });
});
app.listen(8081);