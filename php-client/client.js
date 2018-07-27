/**
 * Connect to backend server.js with:
 * <script src="http://myurl:8080/socket.io/socket.io.js"></script>
 * then trigger an event with included event class.  You should see the event details in your client's console
 * 
 * Example applications: notify browser when a new mail message or transactional e-mail was triggered to them
 * Sending client simply calls event class, event class tells redis pub, socket.io app listens through redis sub and
 * tells connected browser.
 *
 * the 'watch' function below simply subscribes the browser to a particular user_id's channel for notifications to them.
 * Add a 'user_id' member to the Event payload array to make it go only to that user, otherwise all users see it.
 */

try {
	var socket = io.connect('http://localhost:8080/');
	function watch ( user_id ) { // Call this anytime someone logs in so they get events for their ID, @TODO verify a password or something so we know it's really them
		socket.emit("user",user_id);
	}

	socket.on("message", function(payload) {
		console.log("Got event from redis server!");
		console.debug(payload);
	});
} catch ( e ) { console.log(e); }
