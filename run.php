<?php
/*
 * This file is apart of the ValZargaming project.
 *
 * Copyright (c) 2021 Valithor Obsidion <valzargaming@gmail.com>
 */

ini_set('max_execution_time', 0);
define('MAIN_INCLUDED', 1); //Token and SQL credential files are protected, this must be defined to access
$GLOBALS['debug_echo'] = true; //Both Palace and Slash checks for this to determine whether to echo debug prompts or not
 
include 'vendor/autoload.php';
include 'ValZarGaming/ValZarGaming.php';
require __DIR__ .'/../secret.php'; //$secret
require __DIR__ . '/../token.php'; //$token

$loop = React\EventLoop\Factory::create();
$logger = new Monolog\Logger('New logger');
$logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout'));

$discord_options = array(
	'loop' => $loop,
	'socket_options' => [
		'dns' => '8.8.8.8', // can change dns
	],
	'token' => $token,
	'loadAllMembers' => true,
	'storeMessages' => true,
	'logger' => $logger,
	'intents' => \Discord\WebSockets\Intents::getDefaultIntents() | \Discord\WebSockets\Intents::GUILD_MEMBERS, // default intents as well as guild members
);
$discord = new Discord\Discord($discord_options);
$browser = new \React\Http\Browser($discord->getLoop()/*, $connector*/);
include __DIR__ . '/vendor/vzgcoders/palace/stats_object.php';
$stats = new Stats();
$stats->init($discord);

$nick = 'ValZarGaming'; // Twitch username (Case sensitive)
$twitch_options = array(
	//Required
	'secret' => $secret, // Client secret
	'nick' => $nick, 
	'channels' => [
		strtolower($nick), // Your channel
		'daathren', // (Optional) Additional channels
		'smalltowngamingtv',
		'shrineplays',
		'violentvixen_',
		'linkdrako',
		'ebonychimera',
	],
	
	//Optional
	'discord' => $discord, // Pass your own instance of DiscordPHP (https://github.com/discord-php/DiscordPHP)	
	'discord_output' => true, // Output Twitch chat to a Discord server
	'guild_id' => '923969098185068594', //ID of the Discord server
	'channel_id' => '924019611534503996', //ID of the Discord channel to output messages to
	
	'loop' => $loop, // Pass your own instance of $loop to share with other ReactPHP applications
	'socket_options' => [
		'dns' => '8.8.8.8', // Can change DNS provider
	],
	'verbose' => true, // Additional output to console (useful for debugging)
	'debug' => false, // Additional output to console (useful for debugging communications with Twitch)
	
	//Custom commands
	'commandsymbol' => [ // Process commands if a message starts with a prefix in this array
		"@$nick", //Users can mention your channel instead of using a command symbol prefix
		'!',
		';',
	],
	'whitelist' => [ // Users who are allowed to use restricted functions
		strtolower($nick), //Your channel
		'daathren',
		'smalltowngamingtv',
		'shrineplays',
		'violentvixen_',
		'linkdrako',
		'ebonychimera',
	],
	'badwords' => [ // List of blacklisted words or phrases in their entirety; User will be immediately banned with reason 'badword' if spoken in chat
		'Buy followers, primes and viewers',
		'bigfollows . com',
		'stearncomminuty',
	],
	'social' => [ //NYI
		'twitter' => 'https://twitter.com/daathren',
		'instagram' => 'https://www.instagram.com/daathren',
		'discord' => 'https://discord.gg/CpVbC78XWT',
		'tumblr' => 'https://daathren.tumblr.com',
		'youtube' => 'https://www.youtube.com/daathren',
	],
	'tip' => [ //NYI
		'paypal' => 'https://www.paypal.com/paypalme/daathren',
		'cashapp' => '$DAAthren',
	],
	'responses' => [ // Whenever a message is sent matching a key and prefixed with a command symbol, reply with the defined value
		'ping' => 'Pong!',
		'github' => 'https://github.com/VZGCoders/TwitchPHP',
		//'lurk' => 'You have said the magick word to make yourself invisible to all eyes upon you, allowing you to fade into the shadows.',
		//'return' => 'You have rolled a Nat 1, clearing your invisibility buff from earlier. You might want to roll for initiative…',
	],
	'functions' => [ // Enabled functions usable by anyone
		'help', // Send a list of commands as a chat message
	],
	'restricted_functions' => [ // Enabled functions usable only by whitelisted users
		//'so', //Advertise someone else
	],
	'private_functions' => [ // Enabled functions usable only by the bot owner sharing the same username as the bot
		'php', //Outputs the current version of PHP as a message
		'join', //Joins another user's channel
		'leave', //Leave the current user's channel
		'stop', //Kills the bot
	],
	/*
	`HelixCommandClient => [
		$HelixCommandClient, // Optionally pass your own instance of the HelixCommandClient class
	],
	*/
	'helix' => [ // REQUIRES a bot application https://dev.twitch.tv/console/apps 
		'bot_id' => $bot_id,  // Obtained from application
		'bot_secret' => $bot_secret,  // Obtained from application
		'bot_token' => $bot_token,  // Obtained from your own server using twitch_oauth.php (see example at https://www.valzargaming.com/twitch_oauth/twitch_oauth_template.html)
		'refresh_token' => $refresh_token,  // Obtained from your own server using twitch_oauth.php (see example at https://www.valzargaming.com/twitch_oauth/twitch_oauth_template.html)
		'expires_in' => $expires_in,  // Obtained from your own server using twitch_oauth.php (see example at https://www.valzargaming.com/twitch_oauth/twitch_oauth_template.html)
	],
	/*
	'browser' => new \React\Http\Browser($options['loop']), //Optionally pass your own browser for use by Helix' async commands
	*/
);
// Responses that reference other values in options should be declared afterwards
//$twitch_options['responses']['social'] = 'Come follow the magick through several dimensions:  Twitter - '.$twitch_options['social']['twitter'].' |  Instagram - '.$twitch_options['social']['instagram'].' |  Discord - '.$twitch_options['social']['discord'].' |  Tumblr - '.$twitch_options['social']['tumblr'].' |  YouTube - '.$twitch_options['social']['youtube'];
//$twitch_options['responses']['tip'] = 'Wanna help fund the magick?  PayPal - '.$twitch_options['tip']['paypal'].' |  CashApp - '.$twitch_options['tip']['cashapp'];
//$twitch_options['responses']['discord'] = $twitch_options['social']['discord'];
$twitch = new Twitch\Twitch($twitch_options);

$options = array(
	'loop' => $loop,
	'browser' => $browser,
	'discord' => $discord,
	'twitch' => $twitch,
);

$valzargaming = new ValZarGaming\ValZarGaming($options);

$discord->getLoop()->addTimer(86400, function() {
	exit();
});

echo 'cwd: ' . getcwd() . PHP_EOL;

include 'Palace/Palace_include.php'; //Declare Discord event listeners and start the bot
//$valzargaming->run(); //Twitch and Discord start independently inside of Palace_include.php, and for some reason $twitch does not get started with this
?>