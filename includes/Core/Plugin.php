<?php

namespace WPVaaniLog\Core;

defined( 'ABSPATH' ) || exit;

final class Plugin {

    private static ?Plugin $instance = null;

    private Loader $loader;

    private function __construct() {

        $this->loader = new Loader();

    }

    public static function instance(): Plugin {

        if ( self::$instance === null ) {

            self::$instance = new self();

        }

        return self::$instance;

    }

    public function boot(): void {

        $this->loader->boot();

    }

}