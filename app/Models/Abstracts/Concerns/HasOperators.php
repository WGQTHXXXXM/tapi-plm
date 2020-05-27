<?php
namespace App\Models\Abstracts\Concerns;

trait HasOperators
{
    public $operators = true;

    public function updateOperatorsForCreating()
    {
        if ($this->usesOperators()) {
            $user = request()->user();
            if ($user) {
                $this->setCreatedBy($user->id);
                $this->setUpdatedBy($user->id);
            }
        }
        return $this;
    }

    public function updateOperatorsForUpdating()
    {
        if ($this->usesOperators()) {
            $user = request()->user();
            if ($user) {
                $this->setUpdatedBy($user->id);
            }
        }
        return $this;
    }

    public function setCreatedBy($value)
    {
        $this->{$this->getCreatedByColumn()} = $value;
        return $this;
    }

    public function setUpdatedBy($value)
    {
        $this->{$this->getUpdatedByColumn()} = $value;
        return $this;
    }

    public function getCreatedByColumn()
    {
        return static::CREATED_BY;
    }

    public function getUpdatedByColumn()
    {
        return static::UPDATED_BY;
    }

    public function usesOperators()
    {
        return $this->operators;
    }

}
