<?php
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1'); //Unlimited memory usage
//This file was written by Valithor#5947 <@116927250145869826>
//Special thanks to keira#7829 <@297969955356540929> for helping me get this behemoth working after converting from DiscordPHP

//DO NOT VAR_DUMP GETS, most objects like GuildMember have a guild property which references all members
//Use get_class($object) to verify the main object (usually a collection, check src/Models/)
//Use get_class($object->first())to verify you're getting the right kind of object. IE, $author_guildmember->roles should be Models\Role)
//If any of these methods resolve to a class of React\Promise\Promise you're probably passing an invalid parameter for the class
//Always subtract 1 when counting roles because everyone has an @everyone role
$vm = false; //Set this to true if using a VM that can be paused

//use RestCord\DiscordClient;

function execInBackground($cmd) {
    if (substr(php_uname(), 0, 7) == "Windows") {
        pclose(popen("start ". $cmd, "r")); //pclose(popen("start /B ". $cmd, "r"));
    }//else exec($cmd . " > /dev/null &");
}

//Global variables
include getcwd() . '/vendor/vzgcoders/palace/config.php'; //Global config variables
include getcwd() . '/vendor/vzgcoders/palace/game_roles.php'; //Used by the game role picker function
include getcwd() . '/vendor/vzgcoders/palace/species.php'; //Used by the species role picker function
include getcwd() . '/vendor/vzgcoders/palace/sexualities.php'; //Used by the sexuality role picker function
include getcwd() . '/vendor/vzgcoders/palace/gender.php'; //Used by the gender role picker function
include getcwd() . '/vendor/vzgcoders/palace/pronouns.php'; //Used by the pronouns role picker function
include getcwd() . '/vendor/vzgcoders/palace/nsfw_role.php'; //Required to use the NSFW role picker and to view channels marked as NSFW
include getcwd() . '/vendor/vzgcoders/palace/nsfw_subroles.php'; //Used by the NSFW role picker function; TODO
include getcwd() . '/vendor/vzgcoders/palace/channel_roles.php'; //Used by the channel role picker function
include getcwd() . '/vendor/vzgcoders/palace/custom_roles.php'; //Create your own roles with this template!

include getcwd() . '/vendor/vzgcoders/palace/blacklisted_owners.php'; //Array of guild owner user IDs that are not allowed to use this bot
include getcwd() . '/vendor/vzgcoders/palace/blacklisted_guilds.php'; //Array of Guilds that are not allowed to use this bot
include getcwd() . '/vendor/vzgcoders/palace/whitelisted_guilds.php'; //Only guilds in the $whitelisted_guilds array should be allowed to access the bot.

//Custom functions
include_once getcwd() . '/vendor/vzgcoders/palace/custom_functions.php';
//Event listener functions
include_once getcwd() . '/vendor/vzgcoders/palace/functions/message-function.php'; //message()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/messageupdate-function.php'; //messageUpdate()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/messageupdateraw-function.php'; //messageUpdateRaw()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/messagedelete-function.php'; //messageDelete()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/messagedeleteraw-function.php'; //messageDeleteRaw()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/messagereactionadd-function.php'; //messageReactionAdd()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/messagereactionremove-function.php'; //messageReactionRemove()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/guildbanadd-function.php'; // guildBanAdd()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/guildbanremove-function.php'; //guildBanRemove()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/guildmemberadd-function.php'; //guildMemberAdd()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/guildmemberremove-function.php'; //guildMemberRemove()
include_once getcwd() . '/vendor/vzgcoders/palace/functions/guildmemberupdate-function.php'; //guildMemberUpdate()

//include 'commands.php';
//$options['commands'] => $commands; // Import your own Twitch/Commands object to add additional functions

function webapiFail($part, $id) {
	//logInfo('[webapi] Failed', ['part' => $part, 'id' => $id]);
	return new \GuzzleHttp\Psr7\Response(($id ? 404 : 400), ['Content-Type' => 'text/plain'], ($id ? 'Invalid' : 'Missing').' '.$part.PHP_EOL);
}
function webapiSnow($string) {
	return preg_match('/^[0-9]{16,18}$/', $string);
}
$GLOBALS['querycount'] = 0;
$webapi = new \React\Http\Server($loop, function (\Psr\Http\Message\ServerRequestInterface $request) use ($discord) {
	$path = explode('/', $request->getUri()->getPath());
	$sub = (isset($path[1]) ? (string) $path[1] : false);
	$id = (isset($path[2]) ? (string) $path[2] : false);
	$id2 = (isset($path[3]) ? (string) $path[3] : false);
	$ip = (isset($path[4]) ? (string) $path[4] : false);
	$idarray = array(); //get from post data
	
	if ($ip) echo '[REQUESTING IP] ' . $ip . PHP_EOL ;
	if (substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) != '10.0.0')
		echo "[REMOTE_ADDR]" . $request->getServerParams()['REMOTE_ADDR'].PHP_EOL;
	$GLOBALS['querycount'] = $GLOBALS['querycount'] + 1;
	echo 'querycount:' . $GLOBALS['querycount'] . PHP_EOL;
	//logInfo('[webapi] Request', ['path' => $path]);
	switch ($sub) {
		case 'channel':
			if (!$id || !webapiSnow($id) || !$return = $discord->getChannel($id))
				return webapiFail('channel_id', $id);
			break;

		case 'guild':
			if (!$id || !webapiSnow($id) || !$return = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			break;

		case 'bans':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->bans;
			break;

		case 'channels':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->channels;
			break;

		case 'members':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->members;
			break;

		case 'emojis':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->emojis;
			break;

		case 'invites':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->invites;
			break;

		case 'roles':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->roles;
			break;

		case 'guildMember':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			if (!$id2 || !webapiSnow($id2) || !$return = $guild->members->offsetGet($id2))
				return webapiFail('user_id', $id2);
			break;

		case 'user':
			if (!$id || !webapiSnow($id) || !$return = $discord->users->offsetGet($id)) {
				return webapiFail('user_id', $id);
			}
			break;

		case 'userName':
			if (!$id || !$return = $discord->users->get('name', $id))
				return webapiFail('user_name', $id);
			break;

		case 'restart':
			if (substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) != '10.0.0') { //Restricted for obvious reasons
				echo '[REJECT]' . $request->getServerParams()['REMOTE_ADDR'] . PHP_EOL;
				return new \GuzzleHttp\Psr7\Response(501, ['Content-Type' => 'text/plain'], 'Reject'.PHP_EOL);
			}
			$return = 'restarting';
			//execInBackground('cmd /c "'. getcwd()  . '\run.bat"');
			//exec('/home/outsider/bin/stfc restart');
			break;

		case 'lookup':
			if (substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) != '10.0.0') { //This can be abused to cause 429's with Restcord and should only be used by the website. All other cases should use 'user'
				echo '[REJECT]' . $request->getServerParams()['REMOTE_ADDR'] . PHP_EOL;
				return new \GuzzleHttp\Psr7\Response(501, ['Content-Type' => 'text/plain'], 'Reject'.PHP_EOL);
			}
			if (!$id || !webapiSnow($id) || !$return = $discord->users->offsetGet($id))
				return webapiFail('user_id', $id);
			break;

		case 'owner':
			if (substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) != '10.0.0') {
				echo '[REJECT]' . $request->getServerParams()['REMOTE_ADDR'] . PHP_EOL;
				return new \GuzzleHttp\Psr7\Response(501, ['Content-Type' => 'text/plain'], 'Reject'.PHP_EOL);
			}
			if (!$id || !webapiSnow($id))
				return webapiFail('user_id', $id);
			$return = false;
			if ($user = $discord->users->offsetGet($id)) { //Search all guilds the bot is in and check if the user id exists as a guild owner
				foreach ($discord->guilds as $guild) {
					if ($id == $guild->owner_id) {
						$return = true;
						break 1;
					}
				}
			}
			break;

		case 'whitelist':
			if (substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) != '10.0.0') {
				echo '[REJECT]' . $request->getServerParams()['REMOTE_ADDR'] . PHP_EOL;
				return new \GuzzleHttp\Psr7\Response(501, ['Content-Type' => 'text/plain'], 'Reject'.PHP_EOL);
			}
			if (!$id || !webapiSnow($id))
				return webapiFail('user_id', $id);
			$return = false;
			$result = array();
			if ($user = $discord->users->offsetGet($id)) { //If they're not actively in a discord server shared with the bot they probably shouldn't have access to this
				foreach ($discord->guilds as $guild) {
					$target_folder = "\\guilds\\".$guild->id;
					$whitelist_array = array();
					if(!CheckFile($target_folder, "ownerwhitelist.php")) {
						VarSave($target_folder, "ownerwhitelist.php", array());
					}else{
						$whitelist_array = VarLoad($target_folder, "ownerwhitelist.php");
					}
					if ( ($id == $guild->owner_id) || ($id == '116927250145869826') ) { //Valithor and guild owners can access
						$result[] = $guild->id;
					}elseif(!empty($whitelist_array)) { //Check array stored in guild folder to see if they've been added as whitelisted
						foreach ($whitelist_array as $target_id) { //Add the guild ID to an array if access is whitelisted
							if($target_id == $id) $result[] = $guild->id;
						}
					}
				}
				if (!empty($result)) { //Guild IDs
					$return = $result;
				}
			}
			break;

		case 'avatar':
			if (!$id || !webapiSnow($id)) {
				return webapiFail('user_id', $id);
			}
			if (!$user = $discord->users->offsetGet($id)) {
				$discord->users->fetch($id)->done(
					function ($user) {
						$return = $user->avatar;
						return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'text/json'], json_encode($return));
					}, function ($error) {
						return webapiFail('user_id', $id);
					}
				);
				$return = 'https://cdn.discordapp.com/embed/avatars/'.rand(0,4).'.png';
			}else{
				$return = $user->avatar;
			}
			//if (!$return) return new \GuzzleHttp\Psr7\Response(($id ? 404 : 400), ['Content-Type' => 'text/plain'], ('').PHP_EOL);
			break;

		case 'avatars':
			$idarray = $data ?? array(); // $data contains POST data
			$results = [];
			$promise = $discord->users->fetch($idarray[0])->then(function ($user) use (&$results) {
			  $results[$user->id] = $user->avatar;
			});
			
			for ($i = 1; $i < count($idarray); $i++) {
			  $promise->then(function () use (&$results, $idarray, $i, $discord) {
				return $discord->users->fetch($idarray[$i])->then(function ($user) use (&$results) {
				  $results[$user->id] = $user->avatar;
				});
			  });
			}

			$promise->done(function () use ($results) {
			  return new \GuzzleHttp\Psr7\Response (200, ['Content-Type' => 'application/json'], json_encode($results));
			}, function () use ($results) {
			  // return with error ?
			  return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($results));
			});
			break;
		default:
			return new \GuzzleHttp\Psr7\Response(501, ['Content-Type' => 'text/plain'], 'Not implemented'.PHP_EOL);
	}
	return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'text/json'], json_encode($return));
});
$socket = new \React\Socket\Server(sprintf('%s:%s', '0.0.0.0', '55555'), $loop);
$webapi->listen($socket);
$webapi->on('error', function ($e) {
	echo('[webapi] ' . $e->getMessage());
});

$rescue = VarLoad(getcwd() . '/_globals', "RESCUE.php"); //Check if recovering from a fatal crash
if ($rescue) { //Attempt to restore crashed session
    echo "[RESCUE START]" . PHP_EOL;
    $rescue_dir = getcwd() . '/_globals';
    $rescue_vars = scandir($rescue_dir);
    foreach ($rescue_vars as $var) {
        $backup_var = VarLoad(getcwd() . '/_globals', "$var");
                
        $filter = ".php";
        $value = str_replace($filter, "", $var);
        $GLOBALS["$value"] = $backup_var;
        
        $target_dir = $rescue_dir . "/" . $var;
        echo $target_dir . PHP_EOL;
        unlink($target_dir);
    }
    VarSave(getcwd() . '/_globals', "rescue.php", false);
    echo "[RESCUE DONE]" . PHP_EOL;
}
$dt = new DateTime("now"); // convert UNIX timestamp to PHP DateTime
echo "[LOGIN] " . $dt->format('d-m-Y H:i:s') . PHP_EOL; // output = 2017-01-01 00:00:00
try {
    $discord->on('error', function ($error) { //Handling of thrown errors
        echo "[ERROR] $error" . PHP_EOL;
        $exception = null;
        try {
            echo '[ERROR]' . $error->getMessage() . " in file " . $error->getFile() . " on line " . $error->getLine() . PHP_EOL;
        } catch (Throwable $exception) {
        } catch (Exception $e) {
            echo '[ERROR]' . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;
        }
        if ($exception) {
            throw $exception;
        }
    });

    $discord->once('ready', function ($discord) use ($loop, $token, /*$restcord,*/ $stats, $twitch, $browser) {	// Listen for events here
        //$line_count = COUNT(FILE(basename($_SERVER['PHP_SELF']))); //No longer relevant due to includes
        //$version = "RC V1.4.1";
        /*
        $discord->user->setPresence( //Discord status
            array(
                'since' => null, //unix time (in milliseconds) of when the client went idle, or null if the client is not idle
                'game' => array(
                    //'name' => "$line_count lines of code! $version",
                    'name' => $version,
                    'type' => 3, //0, 1, 2, 3, 4 | Game/Playing, Streaming, Listening, Watching, Custom Status
                    'url' => null //stream url, is validated when type is 1, only Youtube and Twitch allowed

                    //Bots are only able to send name, type, and optionally url.
                    //As bots cannot send states or emojis, they can't make effective use of custom statuses.
                    //The header for a "Custom Status" may show up on their profile, but there is no actual custom status, because those fields are ignored.

                ),
                'status' => 'dnd', //online, dnd, idle, invisible, offline
                'afk' => false
            )
        );
        */
        $tag = $discord->user->username . "#" . $discord->user->discriminator;
        echo "[READY] Logged in as $tag (" . $discord->id . ')' . PHP_EOL;
        $dt = new DateTime("now"); // convert UNIX timestamp to PHP DateTime
        echo "[READY TIMESTAMP] " . $dt->format('d-m-Y H:i:s') . PHP_EOL; // output = 2017-01-01 00:00:00
       
		$act  = $discord->factory(\Discord\Parts\User\Activity::class, [
		'name' => 'over the Palace',
		'type' => \Discord\Parts\User\Activity::TYPE_WATCHING
		]);
		$discord->updatePresence($act, false, 'online');
		
        $discord->on('message', function ($message, $discord) use ($loop, $token, $restcord, $stats, $twitch, $browser) { //Handling of a message
			message($message, $discord, $loop, $token, $restcord, $stats, $twitch, $browser);
        });
            
        $discord->on('GUILD_MEMBER_ADD', function ($guildmember) use ($discord) { //Handling of a member joining the guild
			guildMemberAdd($guildmember, $discord);
        });
        
        $discord->on('GUILD_MEMBER_REMOVE', function ($guildmember) use ($discord) { //Handling of a user leaving the guild
			guildMemberRemove($guildmember, $discord);
        });
        
        $discord->on('GUILD_MEMBER_UPDATE', function ($member, $discord, $member_old)/* use ($discord) */{ //Handling of a member getting updated
			guildMemberUpdate($member, $discord, $member_old);
        });
            
        $discord->on('GUILD_BAN_ADD', function ($ban) use ($discord) { //Handling of a user getting banned
			guildBanAdd($ban, $discord);
        });
        
        $discord->on('GUILD_BAN_REMOVE', function ($ban) use ($discord) { //Handling of a user getting unbanned
			guildBanRemove($ban, $discord);
        });
        
        $discord->on('MESSAGE_UPDATE', function ($message_new, $discord, $message_old) { //Handling of a message being changed
			messageUpdate($message_new, $discord, $message_old);
        });
        
        $discord->on('messageUpdateRaw', function ($channel, $data_array) use ($discord) { //Handling of an old/uncached message being changed
			messageUpdateRaw($channel, $data_array, $discord);
        });
        
        $discord->on('MESSAGE_DELETE', function ($message) use ($discord) { //Handling of a message being deleted
			messageDelete($message, $discord);
        });
        
        $discord->on('messageDeleteRaw', function ($channel, $message_id) use ($discord) { //Handling of an old/uncached message being deleted
			messageDeleteRaw($channel, $message_id, $discord);
        });
        
        $discord->on('MESSAGE_DELETE_BULK', function ($messages) use ($discord) { //Handling of multiple messages being deleted
			echo "[messageDeleteBulk]" . PHP_EOL;
            foreach ($messages as $message) messageDelete($message, $discord);
        });
        
        $discord->on('messageDeleteBulkRaw', function ($messages) use ($discord) { //Handling of multiple old/uncached messages being deleted
            echo "[messageDeleteBulkRaw]" . PHP_EOL;
        });
        
        $discord->on('MESSAGE_REACTION_ADD', function ($reaction) use ($discord) { //Handling of a message being reacted to
			if (is_null($reaction->message->content)) {
				//echo '[REACT TO EMPTY MESSAGE]' . __FILE__ . ':' . __LINE__ . PHP_EOL;
				//echo '[MessageID] ' . $reaction->message->id . PHP_EOL;
				$channel = $discord->getChannel($reaction->channel_id);
				$channel->messages->fetch("{$reaction->message_id}")->done(function ($message) use ($reaction, $discord) : void {
					messageReactionAdd($reaction, $discord);
				}, static function ($error) {
					echo $e->getMessage() . PHP_EOL;
				});
			}else messageReactionAdd($reaction, $discord);
        });
        
        $discord->on('MESSAGE_REACTION_REMOVE', function ($reaction) use ($discord) { //Handling of a message reaction being removed
			messageReactionRemove($reaction, $discord);
        });
        
        $discord->on('MESSAGE_REACTION_REMOVE_ALL', function ($message) use ($discord) { //Handling of all reactions being removed from a message
            echo "[messageReactionRemoveAll]" . PHP_EOL;
        });
        
        $discord->on('CHANNEL_CREATE', function ($channel) use ($discord) { //Handling of a channel being created
            echo "[channelCreate]" . PHP_EOL;
        });
        
        $discord->on('CHANNEL_DELETE', function ($channel) use ($discord) { //Handling of a channel being deleted
            echo "[channelDelete]" . PHP_EOL;
        });
        
        $discord->on('CHANNEL_UPDATE', function ($channel) use ($discord) { //Handling of a channel being changed
            echo "[channelUpdate]" . PHP_EOL;
        });
            
        $discord->on('userUpdate', function ($user_new, $user_old) use ($discord) { //Handling of a user changing their username/avatar/etc
            include_once "userupdate-function.php";
			userUpdate($user_new, $user_old, $discord);
        });
            
        $discord->on('GUILD_ROLE_CREATE', function ($role) use ($discord) { //Handling of a role being created
            echo "[roleCreate]" . PHP_EOL;
        });
        
        $discord->on('GUILD_ROLE_DELETE', function ($role) use ($discord) { //Handling of a role being deleted
            echo "[roleDelete]" . PHP_EOL;
        });
        
        $discord->on('GUILD_ROLE_UPDATE', function ($role_new, $role_old) use ($discord) { //Handling of a role being changed
            echo "[roleUpdate]" . PHP_EOL;
        });
        
        $discord->on('voiceStateUpdate', function ($member_new, $member_old) use ($discord) { //Handling of a member's voice state changing (leaves/joins/etc.)
            echo "[voiceStateUpdate]" . PHP_EOL;
        });
        
        $discord->on("error", function (\Throwable $e) {
            echo '[ERROR]' . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;
        });
        
        /*
        $discord->wsmanager()->on('debug', function ($debug) {
            switch ($debug) {
                case "Shard 0 received WS packet with OP code 0": //Spammy
                case "Shard 0 handling WS event PRESENCE_UPDATE": //Spammy
                    break;
                default:
                    echo "[WS DEBUG] $debug" . PHP_EOL;
            }
        });
        */
        
        $discord->on('debug', function ($debug) {
            echo '[DEBUG]' . PHP_EOL;
            $dt = new DateTime("now"); // convert UNIX timestamp to PHP DateTime
            switch ($debug) {
                case 0:
                default:
                    echo "[CLIENT DEBUG] {".$dt->format('d-m-Y H:i:s')."} $debug" . PHP_EOL;
            }
        });
    }); //end main function ready

    $discord->on('disconnect', function ($erMsg, $code) use ($discord, $token, /*$restcord,*/ $vm) {
        //Restart the bot if it disconnects
        //This is almost always going to be caused by error code 1006, meaning the bot did not get heartbeat from Discord
        include "disconnect-include.php";
    });

    set_error_handler(function (int $number, string $message, string $filename, int $fileline) {
        $warn = false;
		
		if ($message != "Undefined variable: suggestion_pending_channel") //Expected to be null
        if ($message != "Trying to access array offset on value of type null") //Expected to be null, part of ;validate*/
        $warn = false;
		
        $skip_array = array();
        $skip_array[] = "Undefined variable";
        $skip_array[] = "Trying to access array offset on value of type null"; //Expected to be null, part of ;validate
        foreach ($skip_array as $value) {
            if (strpos($value, $message) === false) {
                $warn = false;
            }
        }
        if ($warn) {
            echo PHP_EOL . PHP_EOL . "Handler captured error $number: '$message' in $filename on line $fileline" . PHP_EOL . PHP_EOL;
        }
        //die();
    });
    if($twitch) $twitch->run();
    if($discord) $discord->run();
} catch (Throwable $e) { //Restart the bo
    echo "Captured Throwable: " . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine(). PHP_EOL;

    //Rescue global variables
    $GLOBALS["RESCUE"] = true;
    $blacklist_globals = array(
        "GLOBALS",
        "loop",
        "discord",
        "restcord",
        "MachiKoro_Games"
    );
    echo "Skipped: ";
    foreach ($GLOBALS as $key => $value) {
        $temp = array($value);
        if (!in_array($key, $blacklist_globals)) {
            try {
                VarSave(getcwd() . '/_globals', "$key.php", $value);
            } catch (Throwable $e) { //This will probably crash the bot
                echo "$key, ";
            }
        } else {
            echo "$key, ";
        }
    }
    echo PHP_EOL;
  
    //sleep(5);
    
    echo "RESTARTING BOT" . PHP_EOL;
    $discord->destroy();
    $restart_cmd = 'cmd /c "'. getcwd() . '\run.bat"'; //echo $restart_cmd . PHP_EOL;
    //system($restart_cmd);
    execInBackground($restart_cmd);
    die();
}
?> 