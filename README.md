Build a Fitbit Step Contest App with Bluemix, PHP, MySQL, Twitter and Twillio

*Introduction
  Wearable fitness devices are all the rage these days, as is, social media collaboration among friends.  We have combined the two using Bluemix.  Using this application you can quickly create a daily step contest where friends engage in healthy competiton.  A live leaderboard and graphical analytics are available to all contestants along with automatic updates of leaderboard changes via Twitter and Twilio SMS messaging.  Navigating and authenticating with the Fitbit API is a simple task when you fork and reuse this application for your own contest.  Its easy to do and fun. Try it with some of your Fitbit friends today.
What you'll need to build your application
Prerequisites you will need: 
-  A Bluemix account
-  A DevOps Services account
-  Some basic familiarity with PHP, JSON and MySQL (if you need to extend/debug)
-  Fitbit and Twitter accounts set up for read/write API access

The following is a working example you can browse to:
  The URL to your running app: https://www.innovatefit.com

Step 1. Copy the public project and make it your own
1. Open up the DevOps Services public project named FitbitContestApp at https://hub.jazz.net/project/mborowsk/FitbitContestApp/overview
2. Select Edit Code.
3. Copy the entire project by selecting the FORK button at top of screen.
4. Give your project a useful name and take all the defaults. This will create a DevOps Service project for you.
5. Open the file manifest.yml file at the top level of the code tree
6. Change the name and host fields to the name of your new application (make them the same for now).
7. Save the file with File->Save (or control-S).

Step 2. Test Deploy your application
1. Select the Deploy button at the top of the main DevOps Services window. 
2. Select Deploy in the pop up, accepting the default settings.
3. Examine your Bluemix dashboard and see that you have a Running app with the assigned name and a URL formed with the name (ex.  https://name.mybluemix.net).  
If so it means that you have deployed your application along with a PHP run time build pack.

Step 3.  Add an SQL database service to your application
1. In BlueMix go to the Catalog and look under the Services section to find the Data Management subject.
2. Choose the ClearDB MySQL Database service and a dialog should come up showing the free SparkDB version.  You will use this version.
3. On the right side of dialog select your app under the App: pull down.  Leave the defaults for the rest.  Select the Create button.  It will ask you to restage app which you accept.
4. Back on the Bluemix dashboard you should see the service icon that matches the ClearDB Service appear along side you application.  If you click on Show Credentials you will be able to see every thing you need to connect to the database with a DB viewer such as Sequel Pro.

Step 4. Add Twilio SMS service for leaderboard updates Via SMS
1. Add the Twilio service (under mobile) to your app via the dashboard.  You will have to sign up directly at https://www.twilio.com to get your Account SID and Account Token to complete the addition of the service.
2. We are using an active trial account in the example with these credentials: user-fitbitcontest@gmail.com pw-fitbitc0ntest acctSID-AC05088adab848acff6fb1950cbd840128 acctToken-dc72dc32729a6a54476e0265990ab46c.
3. Once you get a full Twilio account register https://YOURAPPURL/php/smsldb.php as the callback url for Twilio to call when someone texts to your Twilio phone number.  The code will add or remove users to/from a database table so they will selectively receive updates as they choose.
4. All "send to" numbers must first be registered with Twilio. Additionally you must set the "TO_NUM" to match in the top of the htdocs/php/smssend.php file.

Step 5. Setup your Fitbit User and Register your Application
1. Register as a new user with www.fitbit.com using a catchy name that everyone will want to friend.  Those who want to be in the contest will have to have their own Fitbit accounts (and devices) and simply become friends with this user.  We used a live Fitbit account in the example with these credentials:  user=fitbitcontest, email=fitbitcontest@gmail.com, pw=fitbitc0ntest).
2. Register your app with Fitbit at https://dev.fitbit.com/apps/new ensuring you register as a web app and for read/write access.  Fitbit will ask for a webapp name and call back.  The web app name is the Bluemix app URL and callback is YOURAPPURL/php/fbit.php but you can change later as needed.
3. Copy down your Client(Consumer) Key and your Client(Consumer) Secret as they will be added to code to use in the oauth process.

Step 6. Setup your Twitter Account
1. Create a Twitter account using your Fitbit mail account.
2. Register your Application for oauth tokens with Twitter via https://apps.twitter.com.  Login with you new account and select create new app.
3. Put in the app name and description.  For the website and callback URL you can simply use your  Bluemix application URL.
4. Accept the conditions and create app. 
5. Under Application Setting/Access level choose to modify app permission and then select read/write as choice.  You will be asked to register a mobile phone with your account also if you have not done so.
6. Navigate to "Keys and Access Tokens" then choose to generate you access token.
You now have your Twitter Id, Consumer Key, Consumer Token, Access Token and Access Token Secret. 

Step 7. Add a User-Defined Service for Fitbit and Twitter 
1. If you have not already installed the Cloud Foundary command line interface to your local machine do so now (http://docs.cloudfoundry.org/devguide/installcf/install-go-cli.html).
2. Run “cf login”  to connect to the Bluemix instance of Cloud Foundry.
3. Run the following commands to add and bind both the Fitbit and Twitter user-defined services to the “FitBitApp” application (we use our app name and credentials for our sample accounts but you will want to use your own):

$ cf cups Fitbit -p '{"FITBIT_CONSUMER_KEY":"66f6ee05201842789ee45eeb1c826411","FITBIT_CONSUMER_SECRET":"1424d4bda97c4619948323419ce8d1ff"}'
Creating user provided service Fitbit in org mborowsk@us.ibm.com / space dev as mborowsk@us.ibm.com...
OK
$ cf bs FitbitApp Fitbit
Binding service Fitbit to app FitbitApp in org mborowsk@us.ibm.com / space dev as mborowsk@us.ibm.com...
OK
TIP: Use 'cf push' to ensure your env variable changes take effect
$ cf cups Twitter -p '{"token":"2815501997-bPZ4Gfp1cNuU5vpVyq8yIVYZw8VL5zmGVt6987p","tokensecret":"yCHDD5InVOh8y910pfR3biADfQdJOBKIs3szUiwmt57bE","key":"swcIoQlPehCrlT34NigaFE1Xy","secret":"53DoauZ1ijhbgeb8VwENDV5CCSw4PhWpg8eMJH402G3gOyR7Kr"}'
Creating user provided service Twitter in org mborowsk@us.ibm.com / space dev as mborowsk@us.ibm.com...
OK
$ cf bs FitbitApp Twitter
Binding service Twitter to app FitbitApp in org mborowsk@us.ibm.com / space dev as mborowsk@us.ibm.com...
OK
TIP: Use 'cf push' to ensure your env variable changes take effect
4. After creating and binding these user-defined service your dashboard should look something like the image above.

Step 7. Initialize your Database
1. Now that you have added the database to your application it knows the credentials to access the database.   In a browser navigate to http://YOURAPPNAME/php/sqlinit.php to initialize the database.
You should see an output that shows the successfully table creation as above.
2. If you want to connect to the database using a viewer you would see something similar to the following.


Step 8. Authenticate your app with Fitbit
1. You will need to login to Fitbit from the app as the user you registered.  Again the example uses a generic fibit account (email=fitbitcontest@gmail.com, pw=fitbitc0ntest) which will work if you want to try.
2. In a browser navigate to this location to login: http://YOURAPPURL/php/fbit.php.  This will start the oauth process and you will then be prompted to log in to Fitbit.  If you have set up your Fitbit user for account everyone to friend you can use those credentials else you can use the example account above. 
A message should come up stating that Oauth tokens were exchanged successfully.   A successful result means that your app now has access to the Fitbit API and those access credentials are now stored in the database.

Step  9. View your leaderboard (and let the games begin)
1. In your browser navigate to the main page and choose the Leaderboard tab.  You should see the list if all is well.  You will only see those who have friended the main Fitbit account of course. 
2. You can test the Twitter function by invoking https://YOURAPPURL/php/tweetsend.php.
3. You can test the Twilio function by invoking https://YOURAPPURL/php/smssend.php.	
		