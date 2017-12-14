<?php

namespace Proto\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{

    protected $table = 'accounts';
    protected $guarded = [/*'id'*/];

    public function products()
    {
        return $this->hasMany('Proto\Models\Product', 'account_id');
    }

}
