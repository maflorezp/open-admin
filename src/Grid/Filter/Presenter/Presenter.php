<?php

namespace OpenAdmin\Admin\Grid\Filter\Presenter;

use OpenAdmin\Admin\Grid\Filter\AbstractFilter;

abstract class Presenter
{
    /**
     * @var AbstractFilter
     */
    protected $filter;

    /**
     * Set parent filter.
     *
     * @param AbstractFilter $filter
     */
    public function setParent(AbstractFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Atajo para fijar el ancho de etiqueta y campo desde el presentador
     * (p. ej. ->select([...])->setCols(3, 6)); lo guarda el filtro.
     *
     * @param int $label
     * @param int $field
     *
     * @return $this
     */
    public function setCols($label = 2, $field = 8)
    {
        $this->filter->setCols($label, $field);

        return $this;
    }

    /**
     * @see https://stackoverflow.com/questions/19901850/how-do-i-get-an-objects-unqualified-short-class-name
     *
     * @return string
     */
    public function view(): string
    {
        $reflect = new \ReflectionClass(get_called_class());

        return 'admin::filter.'.strtolower($reflect->getShortName());
    }

    /**
     * Set default value for filter.
     *
     * @param $default
     *
     * @return $this
     */
    public function default($default)
    {
        $this->filter->default($default);

        return $this;
    }

    /**
     * Blade template variables for this presenter.
     *
     * @return array
     */
    public function variables(): array
    {
        return [];
    }
}
