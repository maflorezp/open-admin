<?php

namespace OpenAdmin\Admin\Widgets;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;

/**
 * Modal de Bootstrap 5 reutilizable.
 *
 * Uso:
 *   $modal = new Modal(['title' => 'Detalle', 'body' => $html]);
 *   echo $modal->render();
 *   // y un disparador: <button data-bs-toggle="modal" data-bs-target="#{$modal->getId()}">
 *
 * El título se escapa; el cuerpo y el pie se imprimen como HTML (igual que Box),
 * así que no deben llevar entrada del usuario sin escapar.
 */
class Modal extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.modal';

    /**
     * Opciones del modal (título, cuerpo, pie, tamaño...).
     *
     * @var array
     */
    protected $options = [];

    /**
     * @param array $options id, title, body, footer, size (sm|lg|xl|''), scrollable, show_loader
     */
    public function __construct($options = [])
    {
        $this->options = array_merge([
            'id'          => uniqid('modal-'),
            'title'       => '',
            'body'        => '',
            'footer'      => '',
            'size'        => 'lg',
            'scrollable'  => true,
            // null = mostrar el indicador de carga solo si el cuerpo está vacío (contenido que llega por ajax)
            'show_loader' => null,
        ], (array) $options);

        $this->class('modal fade');
        $this->tabindex = '-1';
        $this->role = 'dialog';
        $this->setAttribute('aria-hidden', 'true');
    }

    /**
     * Asigna un atributo HTML del contenedor del modal (permite nombres con guion).
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->options['id'] = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->options['id'];
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function title($title)
    {
        $this->options['title'] = $title;

        return $this;
    }

    /**
     * @param string|Renderable|Htmlable $body
     *
     * @return $this
     */
    public function body($body)
    {
        $this->options['body'] = $body;

        return $this;
    }

    /**
     * @param string|Renderable|Htmlable $footer
     *
     * @return $this
     */
    public function footer($footer)
    {
        $this->options['footer'] = $footer;

        return $this;
    }

    /**
     * Tamaño del diálogo: sm, lg, xl o '' para el tamaño por defecto de Bootstrap.
     *
     * @param string $size
     *
     * @return $this
     */
    public function size($size)
    {
        $this->options['size'] = $size;

        return $this;
    }

    /**
     * @param bool $scrollable
     *
     * @return $this
     */
    public function scrollable($scrollable = true)
    {
        $this->options['scrollable'] = (bool) $scrollable;

        return $this;
    }

    /**
     * @param bool $show
     *
     * @return $this
     */
    public function showLoader($show = true)
    {
        $this->options['show_loader'] = (bool) $show;

        return $this;
    }

    /**
     * Convierte el contenido a HTML si es un objeto renderizable.
     *
     * @param mixed $content
     *
     * @return string
     */
    protected function renderContent($content)
    {
        if ($content instanceof Renderable) {
            return $content->render();
        }

        if ($content instanceof Htmlable) {
            return $content->toHtml();
        }

        return (string) $content;
    }

    /**
     * @return string
     */
    public function render()
    {
        $body = $this->renderContent($this->options['body']);
        $footer = $this->renderContent($this->options['footer']);

        $showLoader = $this->options['show_loader'];
        if (is_null($showLoader)) {
            $showLoader = ($body === '');
        }

        $dialogClass = 'modal-dialog';
        if (in_array($this->options['size'], ['sm', 'lg', 'xl'], true)) {
            $dialogClass .= ' modal-'.$this->options['size'];
        }
        if ($this->options['scrollable']) {
            $dialogClass .= ' modal-dialog-scrollable';
        }

        $this->id = $this->options['id'];

        return view($this->view, [
            'attributes'  => $this->formatAttributes(),
            'id'          => $this->options['id'],
            'title'       => $this->options['title'],
            'body'        => $body,
            'footer'      => $footer,
            'dialogClass' => $dialogClass,
            'showLoader'  => $showLoader,
        ])->render();
    }
}
