<?php


namespace App\Models;


use App\Models\Abstracts\Model;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseModel extends Model
{


    protected $dateFormat = 'U.u';

    public static function boot()
    {
        static::addGlobalScope('orderByCreated', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });


        parent::boot();
    }

    //格式化输入的时间  存储进数据库
    public function fromDateTime($value)
    {
        if (is_numeric($value) && (strlen($value) > 11 && strlen($value) < 14)) {
            return $value;
        } elseif ($value instanceof \DateTime) {
            $time = $value->format($this->getDateFormat());

            return (int)($time * 1000);
        }

        return parent::fromDateTime($value);
    }

    //获取UTC毫秒值   数据库获取时间
    protected function asDateTime($value)
    {
        if (is_numeric($value) && (strlen($value) > 11 && strlen($value) < 14)) {
            $value = $value / 1000;
            $format = ceil($value) == $value ? 'U' : 'U.u';
            return \DateTime::createFromFormat($format, $value);


        }
        return parent::asDateTime($value);
    }

    //格式化显示时间
    protected function serializeDate(DateTimeInterface $date)
    {
        if ($this->getDateFormat() == 'U.u') {
            $time = $date->format($this->getDateFormat());
            return (int)($time * 1000);
        }
        return $date->format($this->getDateFormat());
    }


}