<?php
namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Illuminate\Contracts\Support\Arrayable;

class DefaultTransformer extends TransformerAbstract
{
    public function transform(Arrayable $model)
    {
        return $model->toArray();
    }

}

