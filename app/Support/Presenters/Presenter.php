<?php

namespace App\Support\Presenters;

interface Presenter
{
    /**
     * Prepare data to present.
     *
     * @param $data
     *
     * @return array
     */
    public function present($data): array;
}
