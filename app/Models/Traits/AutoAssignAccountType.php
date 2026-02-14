<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;

trait AutoAssignAccountType
{
    protected static function bootAutoAssignAccountType()
    {
        static::creating(function (Model $model) {
            if (empty($model->account_type_id)) {
                $model->account_type_id = 1;
            }
        });
    }
}
