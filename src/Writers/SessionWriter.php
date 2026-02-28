<?php
namespace LazarusPhp\SessionManager\Writers;
use LazarusPhp\QueryBuilder\QueryBuilder;
use LazarusPhp\DateManager\Date;
use LazarusPhp\SessionManager\Interfaces\SessionInterface;
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
        $stmt = QueryBuilder::table($this->config["table"])->select()->where("session_id",$sessionID)->first(PDO::FETCH_ASSOC);
        return $stmt ? $stmt['data'] : '';
    }

    public function write(string $sessionID,string $data):bool
    {
        $date = Date::withAddedTime("now","P".$this->config["days"]."D")->format("y-m-d H:i:s");  
        $params = ["session_id"=>$sessionID,"data"=>$data,"expiry"=>$date];
        return QueryBuilder::table($this->config["table"])->replace($params) ? true : false;
    } 
    public function destroy(string $sessionID): bool
    {
   
        $deleted = QueryBuilder::table($this->config["table"])->delete()->where("session_id",$sessionID)->save();
        return true;
    }

    public function gc(int $maxlifetime = 1400): int
{
    $expiry = Date::create("now")->format("Y-m-d H:i:s");
    $deleted = QueryBuilder::table($this->config["table"])
        ->delete()
        ->where("expiry", "<", $expiry)
        ->save();

    return $deleted ?: 0;
}

    
}