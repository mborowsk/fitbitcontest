The Fitbit Step Contest App allows you to quickly create a website that will access a list of Fitbit friends to display a daily leaderboard.  
As leaders change in realtime tweets and text message are sent out to those subscribed for up to the minute noifications. 
You can look at www.innovatefit.com to see a live example of this code at work.

Here are the steps to reuse the code and customize for you own event:
1)  Fork the entire project (Fork button at top of edit code screen).  Give project a useful name and take all the defaults. 
	This will create a DevOps service project and Bluemix app for you. You can follow this readme in the forked project.
	
2)  Name your app.  In the file manifest.yml and top level of code tree change the "name" and "host" field to your new app name (make them the same for now). 

3)  Test the deploy of your app.  Select the deploy button at top of main DevOps services window.  Select deploy in the pop up.  
	Examine your bluemix dashboard and see that you have running with app with assingned name/route.

4)  Setup a Fitbit user for all Contest participants to friend.
	a.  Register as a new user with fitbit.com using a catchy name that everyone will want to friend. 
		(We used a generic fibit account in example with user=fitbitcontest, email=fitbitcontest@gmail.com, pw=fitbitc0ntest).
	b.  Register your app with fitbit at https://dev.fitbit.com/apps/new ensuring you register as a web app and for read/write access.
		(Fitbit will ask for a webapp name and call back.  The web app name is the bluemix route and callback is "route"/php/fbit.php but you can change later as needed)
	c.  Copy down your Client(Consumer) Key and your Client(Consumer) Secret as they will be added to code to use in the oauth process.
	d.  Add key and secret in the file htdocs/php/fitbitopts.php as below (you will see the keys from the sample app in code):
		define("FITBIT_CONSUMER_KEY", "YOUR KEY HERE"); // CHANGE THIS FOR FITBIT APP KEY
		define("FITBIT_CONSUMER_SECRET", "YOUR SECRET HERE"); // CHANGE THIS FOR FITBIT APP SECRET

5)  Add a database to your app.
	a.  In BlueMix go to the Catalog and look under the Services and the Data Management subject.
	b.  Choose the ClearDB MySQL Database service and a dialog should come up showing the free SparkDB version.  You will use this version.
	c.  On the right side of dialog select your app under the app pull down.  Leave the defaults for the rest.  Select the Create button.  It will ask you to restage app which you accept.
	d.  Back on the Bluemix dashboard you should see the service icon that matchs ClearDB service appear inside your app graphic.
	
6)  Initialize your Database
	a.  Now that you have added the database to your application it knows the credentials to the db.  If you drill into the CleaDB service on Bluemix dashboard you will see credentials there.  
	b.  In browser navigate to this location to initialize the db: http://"your route"/php/sqlinit.php (ex. http://fbfork.mybluemix.net/php/sqlinit.php)
	c.  If you want to see what tables were created in the db just add the credentials to any db viewer (ex. sequel pro) and examine.
	
7)  Authenticate your app with Fitbit
	a.  You will need to login to Fitbit from the app as the user you registered (user for everyone to friend and that has the app associated with it from step 4).  
		The example uses a generic fibit account (email=fitbitcontest@gmail.com, pw=fitbitc0ntest) which will work if you want to try.
		In browser navigate to this location to login: http://"your route"/php/fbit.php. Now we have an Oauth token in db for app to use going forward.
		
8)  View the leaderboard.
	Navigate to the main page and choose the Leaderboard tab.  You should see the list if all is well.  You will only see those who have friended the main fitbit accoutn of course.

9)  Add Twitter Updates for partipants to follow changes in leaderboard
	a.  Create a Twitter account using your Fitbit mail accout.
	b.  Register your for oauth tokens from twitter via https://apps.twitter.com.  Login with you new account and select create new app.
	c.  Put in the app name and description.  For the website and callback URL you can simply use the Bluemix URL or "route" for this app.
	d.  Accept the conditions and create app. 
	e.  Under Appllication Setting/Access level choose to modify app permission and then select read/write as choice.  You will be asked to register a mobile phone with your account also if you have not done so.
	f.  Navigate to "Keys and Access Tokens" then choose to generate you access token.
	g.  You now have your Twitter Id, Consumer Key, Consumer Token, Access Token and Access Token Secret. 
		We will add them to the file htdocs/php/TwitterAuth.php as follows (the actual files use an example account):
		define("SCREEN_NAME", "TWIITER_ID_HERE");
		$twitterSettings = array(
	        'oauth_access_token' => "ACCESS_TOKEN_HERE",
	        'oauth_access_token_secret' => "ACCESS_SECRET_HERE",
	        'consumer_key' => "CONSUMER_KEY_HERE",
	        'consumer_secret' => "CONSUMER_SECRET_HERE"
	    );
	
10)  Add Text message support via Twillio to update partipants of changes in leaderboard
	a.  TBD
	
		