<?php

namespace LazarusPhp\SessionManager;
use LazarusPhp\DateManager\Date;
use LazarusPhp\SessionManager\Interfaces\SessionControl;
use LazarusPhp\SessionManager\CoreFiles\SessionCore;
use Error;
use Exception;
use LazarusPhp\OpenHandler\Handler;
use LazarusPhp\SessionManager\Interfaces\HandlerRules;
use LazarusPhp\SessionManager\Interfaces\SessionInterface;
use LazarusPhp\SessionManager\Writers\SessionWriter;

final class Sessions
{
    private static $instance;
    private array $locked = [];
    private array $config = [];
    private SessionInterface|string|null $handle = null;
    private static $init = false;


    // --- Constructor --- //

    

    private function __construct()
    {
        $this->config = [
        "days" => 7,
        "path"=>"/",
        "table" => "sessions",
        "name" => "sessions",
        "domain" => ".".$_SERVER["HTTP_HOST"] ?? '',
        "secure" => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        "httponly" => false,
        "samesite" => "lax",
        ];
    }

    // --- Create method : entryPoint --- //
    public static function create()
    {
        if(self::$init === false)
        {
            self::$instance =  new self();
            self::$init = true;
            return self::$instance;
        }
        return self::$instance;
    }


    // --- OverWrite config file --- //
    public function withConfig(?array $config=null):self
    {
        // Return the Config;
        if(array_key_exists("config",$this->locked))
        {
            // throw new Exception here to say it cannot be done
            throw new Exception("Ability to initialisae a new config has already been set");
        }

        if(!count($config))
        {
            throw new Exception("No Parameters passed");
        }

        foreach($config as $k => $conf)
        {
            if (array_key_exists($k, $this->config)) 
            {
                // OverWrite the value
                $this->config[$k] = $conf;
            }
        }   


        $this->locked["config"] = true;
        return $this;
    }


    // --- OverWrite with a custom Writer --- //
    public function withWriter(SessionInterface|string $writer)
    {

       if(array_key_exists("writer",$this->locked))
        {
            throw new Exception("Error : Adding a custom Writer has already Been set");
        }

        if(!class_exists($writer))
        {
            throw new Exception("Class $writer does not exist");
        }

        $this->handle = $writer;
        $this->locked["writer"] = true;
        return $this;
    }


    public function save()
    {

    if(isset($this->locked["save"]) && $this->locked["save"] === true)
    {
        throw new Exception("Cannot Reinstantiate save method");
    }

        if(session_status() === PHP_SESSION_ACTIVE)
        {
            return;
        }

        $lifetime = $this->config["days"] * 86400;

        // Instantiate writer if a class name is given
        $handle = is_string($this->handle) ? new $this->handle() : ($this->handle ?? new SessionWriter());

        if (!$handle instanceof SessionInterface) {
            throw new Exception(
                "Session Writer " . (is_object($handle) ? get_class($handle) : (string)$handle) .
                " must implement SessionInterface"
            );
        }

       session_set_save_handler($handle);
        $handle->passConfig($this->config);
        
        session_name($this->config['name']);

        session_set_cookie_params(
            [
            "lifetime" => $lifetime,
            "path" => $this->config["path"],
            "domain"=> $this->config["domain"],
            "secure"=> $this->config["secure"],
            "httponly"=>$this->config["httponly"],
            "samesite"=>$this->config["samesite"],
            ]);
        
    session_start();
    
    if (!isset($_SESSION['init'])) {
        $_SESSION['init'] = true;
        session_regenerate_id(true);
    }

    $this->locked["save"] = true;
    echo session_id();

    }


    // Magic Methods to control Sessions.
    public function __set(string $name, string|int $value)
    {
        $_SESSION[$name] = $value;
    }


    public function __get(string $name)
    {
         return $_SESSION[$name] ?? null;
    }


    public function __isset(string $name)
    {
        return isset($_SESSION[$name]);
    }

    public function __unset(string $name)
    {
        unset($_SESSION[$name]);
    }


    // End Assignment Properties

    public function deleteSessions(...$args)
    {
        if(count($args) === 0)
        {
            session_destroy();
        }
        else
        {
            foreach($args as $arg)
            {
                if (is_array($arg)) {
                    foreach ($arg as $key) {
                        unset($_SESSION[$key]);
                    }
                } else {
                    unset($_SESSION[$arg]);
                }
            }
        }
    }
}