<?php

namespace App\Support\Presenters;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Support\Search\Common\Presenters\PaginatorPresenter;

class PresentablePaginator extends LengthAwarePaginator implements Presentable
{
    use PresentableTrait;

    /**
     * Презентер пагинатора.
     *
     * @return string
     */
    protected $presenter = PaginatorPresenter::class;

    /**
     * Получить дефолтный пагинатор с пустым результатом.
     * @return self
     */
    public static function nullable(): self
    {
        return new self(new PresentableCollection(), 0, config('kp.pagination.limit'));
    }
}
