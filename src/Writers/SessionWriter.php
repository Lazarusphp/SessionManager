<?php
namespace LazarusPhp\SessionManager\Writers;

use LazarusPhp\Database\Facades\DB;
use LazarusPhp\QueryBuilder\QueryBuilder;
use LazarusPhp\DateManager\Date;
use LazarusPhp\Foundation\Facades\DB as FacadesDB;
use LazarusPhp\SessionManager\Interfaces\SessionInterface;
use LazarusPhp\SessionManager\Models\Sessions;
use PDO;
use PDOException;

class SessionWriter Implements SessionInterface
{

    private $config;

    public function passConfig(array $config):void
    {
        $this->config = $config;
    }

       public function open(?string $path,?string $name):bool
    {
        return true;
    }

 
    public function close():bool
    {
        return true;
    }

    public function read(string $sessionID):string
    {
        $stmt = Sessions::where("session_id",$sessionID)->first(PDO::FETCH_ASSOC);
        return $stmt ? $stmt["data"] : '';
    }

    public function updateTimestamp(string $sessionID, string $data): bool
    {
        return $this->write($sessionID, $data);
    }

    public function write(string $sessionID,string $data):bool
    {
    $date = Date::withAddedTime("now","P".$this->config["days"]."D")->format("Y-m-d H:i:s");
    
    $params = [
        "session_id" => $sessionID,
        "data" => $data,
        "expiry" => $date
    ];

        return (bool) Sessions::replace($params);
    } 
    
    public function destroy(string $sessionID): bool
    {
   
        $deleted =  Sessions::where("session_id",$sessionID)->delete();
        return (bool) $deleted;
    }

    public function gc(int $maxlifetime = 1400): int
{
    $expiry = Date::create("now")->format("Y-m-d H:i:s");
    $deleted = 
        Sessions::where("expiry", "<", $expiry)
        ->delete();

    return (int) $deleted;
}

    
}