<?php

namespace OpenAdmin\Admin\Actions;

use Illuminate\Http\Request;
use OpenAdmin\Admin\Grid\Column;

abstract class RowAction extends GridAction
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $row;

    /**
     * @var Column
     */
    protected $column;

    /**
     * @var string
     */
    public $selectorPrefix = '.grid-row-action-';

    /**
     * @var bool
     */
    protected $asColumn = false;

    /**
     * Tooltip (atributo title) del enlace. null = sin tooltip (por defecto);
     * true = usa name(); un string = ese texto.
     *
     * @var string|bool|null
     */
    public $title = null;

    /**
     * Destino del enlace cuando la acción es un href. null = misma ventana (por defecto).
     *
     * @var string|null
     */
    public $target = null;

    /**
     * Get primary key value of current row.
     *
     * @return mixed
     */
    protected function getKey()
    {
        return $this->row->getKey();
    }

    /**
     * Set row model.
     *
     * @param mixed $key
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    public function row($key = null)
    {
        if (func_num_args() == 0) {
            return $this->row;
        }

        return $this->row->getAttribute($key);
    }

    /**
     * Set row model.
     *
     * @param \Illuminate\Database\Eloquent\Model $row
     *
     * @return $this
     */
    public function setRow($row)
    {
        $this->row = $row;

        return $this;
    }

    public function getRow()
    {
        return $this->row;
    }

    /**
     * @param Column $column
     *
     * @return $this
     */
    public function setColumn(Column $column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Show this action as a column.
     *
     * @return $this
     */
    public function asColumn()
    {
        $this->asColumn = true;

        return $this;
    }

    /**
     * @return string
     */
    public function href()
    {
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function retrieveModel(Request $request)
    {
        if (!$key = $request->get('_key')) {
            return false;
        }

        $modelClass = str_replace('_', '\\', $request->get('_model'));

        if ($this->modelUseSoftDeletes($modelClass)) {
            return $modelClass::withTrashed()->findOrFail($key);
        }

        return $modelClass::findOrFail($key);
    }

    /**
     * Activa el tooltip del enlace: sin argumento usa name(), o el texto que se pase.
     *
     * @param string|bool|null $title
     *
     * @return $this
     */
    public function title($title = true)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Abre la acción (si es un href) en otra pestaña. Es opt-in: ninguna acción lo
     * hace por cómo se dibuje (botón o dropdown).
     *
     * @param string|null $target
     *
     * @return $this
     */
    public function target($target = '_blank')
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Atributo title ya escapado, o cadena vacía si no hay tooltip.
     *
     * @return string
     */
    protected function formatTitle()
    {
        if ($this->title === null || $this->title === false || $this->title === '') {
            return '';
        }

        $title = $this->title === true ? $this->name() : $this->title;

        return " title='".e(strip_tags((string) $title))."'";
    }

    /**
     * Atributos target/rel ya escapados, o cadena vacía si no hay destino.
     *
     * @return string
     */
    protected function formatTarget()
    {
        if (empty($this->target)) {
            return '';
        }

        // noopener evita que la pestaña nueva controle a la del panel mediante window.opener
        $rel = $this->target === '_blank' ? " rel='noopener'" : '';

        return " target='".e($this->target)."'".$rel;
    }

    public function display($value)
    {
    }

    /**
     * Render row action.
     *
     * @return string
     */
    public function render()
    {
        $linkClass = ($this->parent->getActionClass() != "OpenAdmin\Admin\Grid\Displayers\Actions\Actions") ? 'dropdown-item' : '';
        $icon = $this->getIcon();

        $title = $this->formatTitle();

        if ($href = $this->href()) {
            $target = $this->formatTarget();

            return "<a href='{$href}' class='{$linkClass}'{$title}{$target}>{$icon}<span class='label'>{$this->name()}</span></a>";
        }

        $this->addScript();

        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='%s {$linkClass}' {$attributes}{$title}>{$icon}<span class='label'>%s</span></a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );
    }
}
