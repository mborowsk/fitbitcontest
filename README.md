Full Article at: http://www.ibm.com/developerworks/library/mo-fitbitcontest-app/

Wearable fitness devices are all the rage these days, as is social media collaboration among friends. We've combined the two by using IBM BluemixTM to build a social app for Fitbit wearers. You can use this application to create a daily step contest quickly in which friends engage in healthy competition. A live leaderboard and graphical analytics are available to all contestants, along with automatic updates of leaderboard changes via Twitter and Twilio SMS messaging. Navigating and authenticating with the Fitbit API is a simple task when you fork and reuse this application for your own contest. It's easy to do and fun.
© Copyright IBM Corporation 2014 Trademarks
Create a Fitbit daily step contest among a group of Fitbit friends by using IBM Bluemix, Twitter, and Twilio. A live leaderboard and graphical analytics are available to all contestants, along with automatic updates of leaderboard changes via Twitter and Twilio SMS messaging.

What you'll need to build your application
• A Bluemix account and a DevOps Services account, both linked to your IBM ID.
• A Twilio account SID and authorization token. (Follow the instructions in Step 4.)
• Fitbit and Twitter accounts set up for read/write API access. (Follow the instructions in Step 5
and Step 6.)
• The Cloud Foundry command line interface.
• Basic familiarity with PHP, JSON, and MySQL (if you need to extend/debug the app).
Run the app Get the code

Step 1. Copy the public project and make it your own
1. Click the Get the code button that's right above this step.
2. In the mborowsk | FitbitContestApp project's overview page on DevOps Services, click the
EDIT CODE button (enter your DevOps Services credentials if you're not already logged in). Click the FORK button, give your project a useful name, and take all the defaults to create a DevOps Service project under your account.
3. Open the manifest.yml file at the top level of the code tree:
4. Change the name and host fields to the name of your new application (make them the same for now). Save the file.

Step 2. Test-deploy your application
1. In the code editor for your DevOps Services project, click the DEPLOY button at the top of the
page.
2. Keep the default settings in the Configure Application Deployment dialog box and click DEPLOY.
3. Log in to Bluemix. In the dashboard, confirm that you have a running app with the assigned name and a URL of the form https://name.mybluemix.net/ (where name is the name of your
app):
If you see the running app, you successfully deployed your application along with a PHP runtime buildpack.

Step 3. Add an SQL database service to your application
1. In the Bluemix catalog, find the Data Management category in the Services section and choose the ClearDB MySQL Database service:
2 You'll use the free SparkDB version of ClearDB for your app. In the dialog box on the right side, select your app from the
App: list and leave the defaults for the rest. Click CREATE:
3. Click Ok when you're prompted to restage the app.
Back in the Bluemix dashboard, you can now see the ClearDB Service icon alongside your application. If you click Show Credentials, you can see everything you need to connect to the database with a DB viewer such as Sequel Pro:

Step 4. Add the Twilio SMS service for leaderboard updates via SMS
1. Select the Twilio service (under Mobile in the catalog) via the dashboard:
2. Sign up for a Twilio account to get your account SID and account token, add them to the Add
Service dialog box, and click CREATE. 
3.
In your Twilio account, register https://yourappurl/php/smsldb.php as the callback URL for Twilio to call when someone texts to your Twilio phone number. The code will add users to or remove users from a database table so that they selectively receive updates as they choose.

Step 5. Set up your Fitbit user and register your application
1. Register as a new user with Fitbit, using a catchy name that everyone will want to friend. Those who want to participate in the contest must have their own Fitbit accounts (and devices) and simply become friends with this user.
2. Register your app on the Fitbit developer site as a web app and for read/write access. When Fitbit asks for a web app name and callback, enter your Bluemix app's URL as the web app name; enter yourappurl/php/fbit.php as the callback. (You can change these settings later if necessary.)
3. Copy or write down your client (consumer) key and your client (consumer) secret, which you'll need for the OAuth process.

Step 6. Set up your Twitter account
1. Create a Twitter account using your Fitbit mail account.
2. Log in to Twitter Application Management with your new account. Click Create New App. 3. Enter the app name and description. For the website and callback URL, use your Bluemix
application URL. Accept the conditions and click Create your Twitter application.
If you use a trial Twilio account, each phone number that sends an SMS message to the Twilio trial number must also be registered as a verified caller ID in the Twilio account setup.
4. In the Application Settings, under Access level, click modify app permissions and then select Read and Write. (You will be asked to register a mobile phone with your account also if you have not already done so.)
5. Browse to Keys and Access Tokens and then generate your access token.
You now have your Twitter ID, consumer key, consumer token, access token, and access token
secret.

Step 7. Add user-defined services for Fitbit and Twitter
1. Run these commands from your OS command line to connect to the Bluemix instance of Cloud Foundry:
2. Run the following four commands to add and bind both the Fitbit and Twitter user-
defined services to your Bluemix application — replacing the placeholders (such as <your_consumer_key_here>) with your credentials and replacing FitbitApp with the name of your application):
cf api https://api.ng.bluemix.net
cf login
$ cf cups Fitbit -p '{"FITBIT_CONSUMER_KEY":"<your_consumer_key_here>", "FITBIT_CONSUMER_SECRET":"<your_consumer_secret_here>"}'
Creating user provided service Fitbit in org
mborowsk@us.ibm.com / space dev as mborowsk@us.ibm.com...
OK
$ cf bs FitbitApp Fitbit
Binding service Fitbit to app FitbitApp in org mborowsk@us.ibm.com / space dev as mborowsk@us.ibm.com...
OK
TIP: Use 'cf push' to ensure your env variable changes take effect
$ cf cups Twitter -p '{"token":"<your_token_key_here>","tokensecret": "<your_tokensecret_key_here>", "key":"<your_key_here>","secret":"<your_secret_here>"}'
Creating user provided service Twitter in org
mborowsk@us.ibm.com / space dev as mborowsk@us.ibm.com..
OK
$ cf bs FitbitApp Twitter
Binding service Twitter to app FitbitApp in
org mborowsk@us.ibm.com / space dev as mborowsk@us.ibm.com...
OK
TIP: Use 'cf push' to ensure your env variable changes take effect
(Your output will refer to your ID instead of mborowsk@us.ibm.com.)
3. After you create and bind these user-defined services, your dashboard should look similar to
this:

Step 8. Initialize your database
1. Now that you've added the database to your application, the app knows the credentials to access the database. Browse to http://yourappname/php/sqlinit.php to initialize the database.
You'll see output that shows successful table creation:
2. If you (optionally) connect to the database with a viewer, you'll be able to see the database
tables:

Step 9. Authenticate your app with Fitbit
1. Browse to http://yourappurl/php/fbit.php. This starts the OAuth process, and you're then prompted to log in to Fitbit:
2. Enter the credentials for the Fitbit user account that you set up in Step 5.
A message should come up stating that OAuth tokens were exchanged successfully. A successful result means that your app now has access to the Fitbit API and that those access credentials are now stored in the database.

Step 10. View your leaderboard (and let the games begin)
1. In your browser, open the main app page and click the Leaderboard tab. If all is well, you can see the contestant list. You'll see only those who have friended the main Fitbit account, of
course:
2. Test the Twitter function by browsing to https://yourappurl/php/tweetsend.php.
3. Test the Twilio function by browsing to https://yourappurl/php/smssend.php?pn=10-
digit_number.

Conclusion
In a few simple steps, you were able to quickly create a PHP app running in the cloud with a database, Fitbit API access, and SMS and Twitter functionality. You accomplished in minutes what would have taken days using traditional methods. Your friends who use Fitbit can now enjoy a dedicated website for tracking a healthy step competition. As wearable health devices from different manufacturers become more popular, you can easily extend this application, enabling contestants with any device to participate. You're ready to "step" into the future by taking advantage of the rapid development productivity of Bluemix.