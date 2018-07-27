#!/usr/bin/node

var io = require('socket.io').listen(8080);
var redis = require('redis');

// Publishing a message somewhere
var pub = redis.createClient();
pub.on("error", function(err) {
   console.log("Redis pub error: " + err);
   setTimeout("pub.connect();",10000);
});

var connected = [];

io.sockets.on('connection', function (socket) {
    var sub = redis.createClient();

    sub.subscribe("global");

    sub.on("message", function(channel, message) {
        try {
            var payload = JSON.parse(message.payload);
            socket.emit( message.event, payload );
        } catch ( e ) {
            console.log(e);
        }
    });

    sub.on("error", function(err) {
        console.log("Redis sub error: " + err);
    });

    socket.on('user', function(user_id) {
        socket.user_id=user;
	socket.subscribe("user" + socket.user_id);
    });
});
