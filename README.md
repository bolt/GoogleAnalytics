Google Analytics
================

Upgrading to V3 of Plugin
-------------------------
When updating to V3 please change `key_file_location` to `key_file`.

Overview
--------
This extension inserts the Google Analytics tracker code on your pages. The plugin 
also allows you view the Google Analytics statistics in the backend as well by going to 
`extend/Statistics`. 

Displaying Google Analytics on every page
-----------------------------------------
In order to display the Google Analytics tracking code on every page of the 
website, please update the `config.yml` and edit `webproperty-id`, `universal`, or 
`universal_domainname`. The backend can be turned off by setting `backend` to false.

Displaying Google Statistics on the backend
-------------------------------------------
In order to display the Google Analytics tracking code on every page of the 
website, please update the `config.yml` and edit `key_file` and `service_account_email`.
The `ga_profile_id` is not needed anymore. You can specifiy the `ga_profile_id`, 
if you would like still. 

**Verbatim** instructions can be found here: https://developers.google.com/analytics/devguides/reporting/core/v3/quickstart/service-php#enable

Use the developers console and replace the values with your service account
email, and absolute location of your key file. You can generate the needed
details at https://console.developers.google.com. 

Here's a quick 'n' dirty step-by-step:

  1. Go to the Google API Console and create a new app/project
  2. In the Services tab, flip the Google Analytics switch to on
  3. Click the credentials tab
  4. Click 'Create Credentials' > 'Service Account Key'
  5. Under 'Service Account', select 'New Service Account', give it a name and
     remember the email it generates for you. That is the
     'service_account_email'
  6. Select the p12 format
  7. Download the key and then upload it to your server.
  8. Visit your google analytics admin and add the service account email under
     User Management with the 'read & analyze' permissions
  9. Update the google analytics config.yml with the key file name and put the
     file under app/config/extensions/ and service account email.

If the `.p12` file doesn't work with the version of google code in this extension you may have to convert it to a `.pem` file with this command:

```
openssl pkcs12 -in ga-key-file.p12 -out ga-key-file.pem -nodes
```

The command will ask for the password which is 'notasecret'. 
