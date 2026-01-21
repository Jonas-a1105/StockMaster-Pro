<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use \PDO;

class ApiKeyModel extends BaseModel {
    protected $table = 'api_keys';

    public function __construct() {
        parent::__construct();
    }

    public function validateKey($key) {
        $result = $this->query()
            ->select(['user_id'])
            ->where('api_key', $key)
            ->where('activo', 1)
            ->whereRaw("(expira IS NULL OR expira > ?)", [date('Y-m-d H:i:s')])
            ->first();
        return $result ? $result['user_id'] : null;
    }

    public function getKeysByUser($userId) {
        return $this->query()->where('user_id', $userId)->get();
    }
}
