<?php
namespace LazarusPhp\SessionManager\Interfaces;

use SessionHandlerInterface;

interface SessionInterface extends SessionHandlerInterface
{
    public function passConfig(array $config):void;
}