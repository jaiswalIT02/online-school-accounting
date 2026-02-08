<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;

trait AutoAssignSessionYear
{
    protected static function bootAutoAssignSessionYear()
    {
        static::creating(function (Model $model) {
            if (empty($model->session_year_id)) {
                $model->session_year_id = current_session_year_id();
            }
        });
    }
}
