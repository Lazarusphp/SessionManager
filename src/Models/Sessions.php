<?php
namespace LazarusPhp\SessionManager\Models;
use LazarusPhp\QueryBuilder\Traits\Relationships;
use App\Http\Model\Posts;
use LazarusPhp\Foundation\Model\Model;

class Sessions extends Model
{
    // Store all “real” methods in an internal array

    protected $allowed = [];
    protected string $primaryKey = "id";
    protected string $table = "sessions";
    protected string $model = "";

    // Require Relations Trait
    use Relationships;


}