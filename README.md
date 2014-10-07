The Fitbit Step Contest App allows you to quickly create a website that will access a list of Fitbit friends to display a daily leaderboard.  As leaders change in realtime tweets and text message are sent out to those subscribed for up to the minute noifications. You can look at www.innovatefit.com to see a live example of this code at work.

Here are the steps to reuse the code and customize for you own event:

1)  Setup a Fitbit user for all Contest participants to friend.
	a.  Register as a new user with fitbit using a catchy name that everyone will want to friend.
	b.  Register your app with fitbit at https://dev.fitbit.com/apps/new ensuring you register as a web app and for read/write access.
	c.  Copy down your Client(Consumer) Key and your Client(Consumer) Secret as they will be added to code to use on the oauth process.
	d.  In the file htdocs/php/fitbitopt.php as below:
		define("FITBIT_CONSUMER_KEY", "YOUR KEY HERE"); // CHANGE THIS FOR FITBIT APP KEY
		define("FITBIT_CONSUMER_SECRET", "YOUR SECRET HERE"); // CHANGE THIS FOR FITBIT APP SECRET

2)  Add a database to your app.
	a.  In BlueMix go to the Catalog and look under the Data Management subject.
	b.  Choose the ClearDB Database service and choose your app to connect it to when configuring		