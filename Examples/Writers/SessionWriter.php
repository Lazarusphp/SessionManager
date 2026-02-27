<?php
namespace LazarusPhp\SessionManager\Writers;
use LazarusPhp\QueryBuilder\QueryBuilder;
use LazarusPhp\DateManager\Date;
use LazarusPhp\SessionManager\Interfaces\HandlerRules;
use LazarusPhp\SessionManager\Interfaces\SessionInterface;
use SessionhandlerInterface;
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
    public function read(string $sessionID):string | false
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
   
        return QueryBuilder::table($this->config["table"])->delete()->where("session_id",$sessionID)->save() ? true : false;
    }

    public function gc(int $maxlifetime=1400):int|false
    {
        $expiry = Date::create("now");
        $expiry = $expiry->format("y-m-d h:i:s");
        
        try {
            return QueryBuilder::table($this->config["table"])->delete()->where("expiry","<",$expiry) ? true : false;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() . $e->getCode());
            return false;
        }
    }

    
}