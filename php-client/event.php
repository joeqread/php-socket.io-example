<?php
/**
 * This is an inter-class messaging transport, basically it lets you send a message that can be received anywhere
 * @todo figure out how to hook codeigniter controllers to 'listen' method without instantiating them first... Config file?
 * @author joe
 */

class Event {
	private static $redis = null;
	private static $listeners = null;

	/**
	 * Attach a listener for an event
	 * @param $event - Event name to listen for
	 * @param $callback - function or class/method to call when event happens, only argument will be payload
	 *
	 * @return string $id - Unique listener ID, so you can later remove the listener from receiving events
	 */
	public static function listen ( $event, &$callback ) {
		$id=uniqid("listener");
		self::$listeners[$event][$id]=$callback;
		return $id;
	}

	/**
	 * Stops listening for events
	 *
	 * @param $event - Event you originally were listening for
	 * @param $id - id returned by $this->listen method
	 * @see self::listen
	 * @return bool - did we detach that listener, or not?
	 */
	public static function remove_listener ( $event, $id ) {
		if ( isset(self::$listeners[$event][$id]) ) {
			unset(self::$listeners[$event][$id]);
			return true;
		}
		return false;
	}

	/**
	 * Trigger an event, which will go to all listeners (and Redis pub/sub)
	 *
	 * @param (string) $event - Event name you are triggering
	 * @param $payload - Details to be passed to listeners.  Please do not pass object instances, no de/serialization here
	 * @example $this->load->library("Event");
	 *          Event::trigger("onUserLoggedin", array("user_id" => $user_id));
	 */
	public static function trigger ( $event, $payload ) {
		error_log("Event class: got event '$event' with payload: " . json_encode($payload,JSON_FORCE_OBJECT & JSON_UNESCAPED_SLASHES));

		if ( is_null(self::$redis) ) {
			self::$redis = new Redis();
			self::$redis->connect("127.0.0.1");
		}

		self::$redis->publish( "event", json_encode( array("event" => $event, "payload" => $payload), JSON_FORCE_OBJECT & JSON_UNESCAPED_SLASHES));

		if ( isset(self::$listeners[$event]) ) {
			foreach ( self::$listeners[$event] as $id => &$callback ) {
				call_user_func( $callback, $event, $payload );
			}
			reset(self::$listeners[$event]);
		}

		if ( isset($payload["user_id"]) ) {
			$this->redis->publish( "user" . $payload["user_id"], json_encode($payload,true) );
		} else {
			$this->redis->publish( "global", json_encode($payload,true) );
		}
	}
} 
