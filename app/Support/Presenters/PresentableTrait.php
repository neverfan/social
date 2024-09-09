<?php

namespace App\Support\Presenters;

use App\Exceptions\Presenters\NoPresenterException;

/**
 * Trait PresentableTrait.
 *
 * @package App\Support\Presenters
 */
trait PresentableTrait
{
    /**
     * Задать презентер
     *
     * @param ?string $presenter
     * @return $this
     */
    public function setPresenter(?string $presenter): static
    {
        $this->presenter = $presenter;

        return $this;
    }

    /**
     * Назначен ли презентер.
     *
     * @return bool
     */
    public function hasPresenter(): bool
    {
        return (property_exists($this, 'presenter') && !empty($this->presenter))
            || (property_exists($this, 'itemPresenter') && !empty($this->itemPresenter));
    }

    /**
     * Возвращает представление объекта если назначен презентер.
     *
     * @throws NoPresenterException
     * @return array
     */
    public function present(): array
    {
        if (!$this->hasPresenter()) {
            return throw new NoPresenterException($this::class);
        }

        return $this->makePresenter()->present($this);
    }

    /**
     * Инициализирует инстанс презентера.
     *
     * @return Presenter
     */
    private function makePresenter(): Presenter
    {
        return app($this->presenter);
    }
}
