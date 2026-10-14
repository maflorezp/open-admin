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
     * Callback que decide, fila por fila, si la acción se dibuja.
     *
     * @var \Closure|null
     */
    protected $beforeRenderCallback;

    /**
     * Clases CSS extra para el enlace de la acción.
     *
     * @var string
     */
    protected $cssClass = '';

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
     * Registra un callback que se evalúa antes de dibujar la acción. Se ejecuta con
     * $this ligado a la acción (tiene $this->row) y recibe la acción como argumento;
     * si devuelve false la acción no se dibuja en esa fila.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function beforeRender(\Closure $callback)
    {
        $this->beforeRenderCallback = $callback;

        return $this;
    }

    /**
     * Indica si la acción debe dibujarse en la fila actual.
     *
     * @return bool
     */
    public function shouldRender()
    {
        if ($this->beforeRenderCallback instanceof \Closure) {
            return (bool) $this->beforeRenderCallback->call($this, $this);
        }

        return true;
    }

    /**
     * Agrega clases CSS al enlace de la acción.
     *
     * @param string $class
     *
     * @return $this
     */
    public function cssClass($class)
    {
        $this->cssClass = trim($this->cssClass.' '.$class);

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

        return " title='".e(trim(strip_tags((string) $title)))."'";
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
        if (!$this->shouldRender()) {
            return '';
        }

        $linkClass = ($this->parent->getActionClass() != "OpenAdmin\Admin\Grid\Displayers\Actions\Actions") ? 'dropdown-item' : '';
        if ($this->cssClass !== '') {
            $linkClass = trim($linkClass.' '.e($this->cssClass));
        }
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
