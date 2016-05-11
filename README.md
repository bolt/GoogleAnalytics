Google Analytics
================

This extension inserts the Google Analitics tracker code in your pages. Edit the `config.yml` file so
it contains the correct 'webproperty-id', 'ga_profile_id', 'key_file_location', and 'service_account_email'.
(If upgrading to new version copy the missing configuration from the distributed yml file to get the new settings)

Use the developers console and replace the values with your service account email, and absolute location of your key file.
You can generate the needed details at https://console.developers.google.com.
Here's a quick 'n' dirty step-by-step:

1. Go to the Google API Console and create a new app/project
2. In the Services tab, flip the Google Analytics switch to on
3. Click the credentials tab
4. Click 'Service Account Key'
5. Select 'New Service Account' give it a name and remember the email it generates for you. That is the 'service_account_email'
6. Select the p12 format
7. Download the key and then upload it to your server.
8. Visit your google analytics admin and add the service account email under User Management with the 'read & analyze' permissions
9. Update the google analytics config.yml with the key file name and put the file under app/config/extensions/ and service account email. **

** Verbatim instructions can be found here: https://developers.google.com/analytics/devguides/reporting/core/v3/quickstart/service-php#enable

** The p12 file didn't work with the version of google code in this extension you may have to convert it to a pem file with this command

openssl pkcs12 -in ga-key-file.p12 -out ga-key-file.pem -nodes

The command will ask for the password which is 'notasecret'. 
