<?php

/*
 * This file is apart of the ValZargaming project.
 *
 * Copyright (c) 2021 Valithor Obsidion <valzargaming@gmail.com>
 */

namespace ValZarGaming;

class ValZarGaming
{
	public $loop;
	public $browser;
	
	public $discord;
	public $twitch;
	
	protected $verbose = true;
	
	public function __construct(array $options = [])
    {
		$options = $this->resolveOptions($options);
		
		$this->loop = $options['loop'];
		$this->browser = $options['browser'];
		
		if ($options['discord'] || $options['discord_options']) {
			if($options['discord']) $this->discord = $options['discord'];
			elseif($options['discord_options']) $this->discord = new \Discord\Discord($options['discord_options']);
		}
		
		if ($options['twitch'] || $options['twitch_options']) {
			if($options['twitch']) $this->twitch = $options['twitch'];
			elseif($options['twitch_options']) $this->twitch = new Twitch\Twitch($options['twitch_options']);
		}
	}
	
	/*
	* Attempt to catch errors with the user-provided $options early
	*/
	protected function resolveOptions(array $options = []): array
	{
		if ($this->verbose) $this->emit('[VALZARGAMING] [RESOLVE OPTIONS]');
		$options['loop'] = $options['loop'] ?? Factory::create();
		$options['browser'] = $options['browser'] ?? new \React\Http\Browser($options['loop']);
		//Discord must be Discord or null
		//Twitch must be Twitch or null
		return $options;
	}
	
	public function emit(string $string): void
	{
		echo "[EMIT] $string" . PHP_EOL;
	}
	
	public function run(): void
	{
		if ($this->verbose) $this->emit('[VALZARGAMING] [RUN]');
		if(!(isset($this->discord))) $this->emit('[WARNING] Discord not set!');
		else $this->discord->run();
		if(!(isset($this->twitch))) $this->emit('[WARNING] Twitch not set!');
		else $this->twitch->run();
	}
}