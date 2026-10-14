<?php

namespace OpenAdmin\Admin\Grid\Column;

use OpenAdmin\Admin\Grid\Model;

class RadioFilter extends Filter
{
    /**
     * @var array
     */
    protected $options;

    /**
     * RadioFilter constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;

        $this->class = [
            'all'  => uniqid('column-filter-all-'),
            'item' => uniqid('column-filter-item-'),
        ];
    }

    /**
     * Valor elegido, o null si no hay uno válido (vacío, o un arreglo mal formado en la URL).
     *
     * @return string|null
     */
    protected function getSelectedValue()
    {
        $value = $this->getFilterValue();

        if (is_array($value) || is_null($value) || (string) $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * Add a binding to the query.
     *
     * @param mixed $value
     * @param Model $model
     */
    public function addBinding($value, Model $model)
    {
        // Solo un valor suelto; «0» es un valor válido, por eso no se usa empty()
        if (is_array($value) || is_null($value) || (string) $value === '') {
            return;
        }

        $model->where($this->getColumnName(), $value);
    }

    /**
     * Render this filter.
     *
     * @return string
     */
    public function render()
    {
        $value = $this->getSelectedValue();
        $name = e($this->getColumnName());

        $lists = collect($this->options)->map(function ($label, $key) use ($value, $name) {
            $checked = (!is_null($value) && (string) $key === $value) ? 'checked' : '';
            $key = e($key);
            $label = e($label);

            return <<<HTML
<li class="" style="margin: 0;">
    <label style="width: 100%;padding: 3px;">
        <input type="radio" class="{$this->class['item']}" name="{$name}" value="{$key}" {$checked}/>&nbsp;&nbsp;&nbsp;{$label}
    </label>
</li>
HTML;
        })->implode("\r\n");

        // «Todos» queda marcado si no hay valor o si el de la URL no es una de las opciones
        $known = !is_null($value) && in_array($value, array_map('strval', array_keys($this->options)), true);
        $allCheck = $known ? '' : 'checked';
        $active = is_null($value) ? '' : 'text-yellow';

        return <<<EOT
<span class="dropdown">
<form action="{$this->getFormAction()}" pjax-container method="get" style="display: inline-block;">
    {$this->getPreservedHiddenInputs()}
    <a href="javascript:void(0);" class="dropdown-toggle {$active}" data-bs-toggle="dropdown">
        <i class="icon-filter"></i>
    </a>
    <ul class="dropdown-menu" role="menu" style="padding: 10px;box-shadow: 0 2px 3px 0 rgba(0,0,0,.2);left: -70px;">

        <li>
            <ul style='padding: 0;'>
            <li class="" style="margin: 0;">
                <label style="width: 100%;padding: 3px;">
                    <input type="radio" class="{$this->class['all']}" name="{$name}" value="" {$allCheck}/>&nbsp;&nbsp;&nbsp;{$this->trans('all')}
                </label>
            </li>
                <li><hr class="dropdown-divider" /></li>
                {$lists}
            </ul>
        </li>
        <li><hr class="dropdown-divider" /></li>
        <li class="text-right">
            <button class="btn btn-sm btn-flat btn-primary pull-left" data-loading-text="{$this->trans('search')}..."><i class="icon-search"></i>&nbsp;&nbsp;{$this->trans('search')}</button>
            <span><a href="{$this->getFormAction()}" class="btn btn-sm btn-light btn-default"><i class="icon-undo"></i></a></span>
        </li>
    </ul>
</form>
</span>
EOT;
    }
}
