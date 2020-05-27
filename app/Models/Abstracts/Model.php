<?php
namespace App\Models\Abstracts;

use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class Model extends EloquentModel
{
    use Concerns\HasOperators;

    const CREATED_BY = 'created_by';
    const UPDATED_BY = 'updated_by';

    public $incrementing = false;

    protected $primaryKey = 'id';
    protected $keyType = 'string';

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = $model->generateUuid();
            $model->updateOperatorsForCreating();
        });
        self::updating(function ($model) {
            $model->updateOperatorsForUpdating();
        });
    }

    /**
     * 生成 32 个字符的 UUID
     * @return string
     */
    public function generateUuid()
    {
        return str_replace("-", "", Uuid::generate()->string);
    }

}
