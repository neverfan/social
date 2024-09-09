<?php

namespace App\Support\Presenters;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Trait PresentProducerTrait.
 *
 * @package App\Support\Presenters
 */
trait PresentProducerTrait
{
    /**
     * Произвести преобразование данных.
     *
     * @param $data
     * @return mixed
     */
    protected function producePresent($data): mixed
    {
        if ($data instanceof Presentable) {
            return $data->present();
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        return $data;
    }
}
