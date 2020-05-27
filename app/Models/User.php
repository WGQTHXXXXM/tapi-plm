<?php

namespace App\Models;

use App\Auth\Contracts\UserResolver;
use App\Exceptions\LogicException;
use Illuminate\Contracts\Auth\Authenticatable;

class User extends Abstracts\Model implements Authenticatable, UserResolver
{
    public $operators = false;
    public static $detailUser;
    //
    const USER_NORMAL  = 'normal';//在职启用
    const USER_INVALID = 'invalid';//在职禁用
    const USER_LEAVE = 'leave';//离职

    protected $table = 'users';
    protected $fillable = [
        'name', 'user_id', 'phone', 'email', 'status','ding_userid','ding_unionid',
    ];

    public $fields_where = ['id', 'user_id', 'phone', 'created_by', 'updated_by'];
    public $fields_where_in = [//参数格式：status_in
        //'status_in' => 'status'
    ];
    public $fields_where_like = ['name', 'email','status'];
    public $fields_where_between = ['created_at', 'updated_at'];
    public $fields_or_where = [//参数格式：status_or
        //'status_or' => 'status',
    ];
    //关联表查询字段
    public $fields_where_ref = [//参数格式：with-groups-name
        'department' => ['name'],
    ];
    public $fields_where_in_ref = [//参数格式：with-groups-id-in
        //'groups' => ['id', 'status'],
        //'test' => ['id'],//测试数据，支持多表
    ];
    public $fields_where_like_ref = [//参数格式：with-groups-name-like
        //'groups' => ['name', 'description'],
    ];
    public $fields_where_between_ref = [//参数格式：with-groups-created_at_start,with-groups-created_at_end
        //'groups' => ['created_at', 'updated_at']
    ];
    public $fields_or_where_ref = [//参数格式：with-groups-id-or
        //'groups' => ['id', 'name'],
    ];

    public function canAuthenticated()
    {
        return $this->status == static::USER_NORMAL;
    }

    //////////////////关联////////////////////////////////

    ///关联部门表
    public function department()
    {
        return $this->belongsToMany(Departments::class,'department_user_ref','user_id','department_id');
    }

    //关联中间表
    public function dptUserRef()
    {
        return $this->hasMany(DepartmentUserRef::class,'user_id','id');
    }

    // ==================== 数据存取操作 ==================== //
    public static function findByPhone($phone)
    {
        return static::where('phone', $phone)->first();
    }

    public static function findByUserId($userId)
    {
        return static::where('user_id', $userId)->first();
    }


    // ==================== Start implement functions of App\Auth\Contracts\UserResolver  ==================== //
    public static function retrieveById($id)
    {
        return static::find($id);
    }

    public static function retrieveByToken($token)
    {
        $authUser = AuthUser::retrieveByToken($token);

        if (is_null($authUser)) {
            return null;
        }

        $user = static::findByUserId($authUser->uid);
        if (is_null($user) && !empty($authUser->userInfoView->phone)) {
            $user = static::findByPhone($authUser->userInfoView->phone);
            if (!is_null($user)) {
                $user->user_id = $authUser->uid;
                $user->save();
            }
        }

        if (!is_null($user) && $user->canAuthenticated()) {
            self::$detailUser = $user->toArray();
            return $user;
        }

        return null;
    }
    // ==================== End implement functions of App\Auth\Contracts\UserResolver  ==================== //

    // ==================== Start implement functions of Illuminate\Contracts\Auth\Authenticatable  ==================== //
    public function getAuthIdentifierName()
    {
        return $this->primaryKey;
    }

    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return '';
    }

    public function setRememberToken($value)
    {
        return true;
    }

    public function getRememberTokenName()
    {
        return '';
    }
    // ==================== End implement functions of Illuminate\Contracts\Auth\Authenticatable  ==================== //

    // ===============所有用户的id是给定的==== //
    private $userCenterId;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if(!empty($attributes['id']))
            $this->userCenterId = $attributes['id'];
    }
    public function generateUuid()
    {
        if(empty($this->userCenterId))
            throw new LogicException('用户id不可以为空');
        return $this->userCenterId;
    }
    // ===============所有用户的id是给定的==== //
}
