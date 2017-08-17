<?php
/**
 * Created by PhpStorm.
 * User: daan
 * Date: 8/17/17
 * Time: 2:34 PM
 */

namespace StudioSeptember\Sendgrid;

use Illuminate\Mail\TransportManager;
use Illuminate\Support\ServiceProvider;

class SendgridServiceProvider extends ServiceProvider {
	public function register()
	{
		$this->app->afterResolving(TransportManager::class, function(TransportManager $manager) {
			$this->extendTransportManager($manager);
		});

	}

	public function extendTransportManager(TransportManager $manager)
	{
		$manager->extend('sendgrid', function() {
			$config = $this->app['config']->get('services.sendgrid', array());
			return new Transport($config['key']);
		});
		
	}

}