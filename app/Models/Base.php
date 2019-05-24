<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class Base extends Model
{
    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $dateFormat = 'U';

    public $timestamps = true;


    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getTable($withDb = false)
    {
        $table = parent::getTable();
        return $withDb ? sprintf('%s.%s', $this->getConnection()->getDatabaseName(), $table) : $table;
    }
}