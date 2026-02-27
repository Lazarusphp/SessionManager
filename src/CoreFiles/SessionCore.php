<?php

namespace LazarusPhp\SessionManager\CoreFiles;
use SessionHandlerInterface;
use LazarusPhp\QueryBuilder\QueryBuilder;
use LazarusPhp\DateManager\Date;
use PDOException;
use Pdo;

use Exception;
use LazarusPhp\SessionManager\Interfaces\SessionInterface;

class SessionCore implements SessionInterface
{
    private $errors = [];
    private $config = [];

    public function setConfig(array $config)
    {
        $this->config = ["days" => 7, "table" => "sessions"];

        if (count($config) > 0) { {
                foreach ($config as $key => $value) {
                    if (array_key_exists($key, $this->config)) {
                        // OverWrite the value
                        $this->config[$key] = $value;
                    }
                }
                // Merge
            $this->config = array_merge($config,$this->config);
   
            }
        }
        return $this->config;
    }

    public function passConfig(array $config):void
    {
        $this->config = $config;
    }
    
    private function getUnsupportedKeys($keys, $supportedKeys)
    {
        $unsupported = array_diff($keys, $supportedKeys);
        if ($unsupported) {
            foreach ($unsupported as $unsupported) {
                $this->errors[] = "Error Found with Key : $unsupported is not supported";
            }
        }
    }


    // Implementation 

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
