<?php

namespace App\Support\Presenters;

/**
 * Interface Presentable.
 * @package App\Support\Presenters
 */
interface Presentable
{
    /**
     * @param string|null $presenter
     *
     * @return $this
     */
    public function setPresenter(?string $presenter): static;

    /**
     * @return array
     */
    public function present(): array;

    /**
     * @return bool
     */
    public function hasPresenter(): bool;
}
