<?php

namespace OpenAdmin\Admin\Form\Field;

use Illuminate\Support\Arr;
use OpenAdmin\Admin\Admin;
use OpenAdmin\Admin\Form\Field;
use OpenAdmin\Admin\Form\Field\Traits\Sortable;

class ListField extends Field
{
    use Sortable;
    /**
     * @var array
     */
    protected $value = [''];

    /**
     * Fill data to the field.
     *
     * @param array $data
     *
     * @return void
     */
    public function fill($data)
    {
        $this->data = $data;

        $this->value = Arr::get($data, $this->column, $this->value);
        if (!is_array($this->value)) {
            $this->value = json_decode($this->value);
        }
        if (empty($this->value)) {
            $this->value = [''];
        }

        $this->formatValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getValidator(array $input)
    {
        if ($this->validator) {
            return $this->validator->call($this, $input);
        }

        if (!is_string($this->column)) {
            return false;
        }

        $rules = $attributes = [];

        if (!$fieldRules = $this->getRules()) {
            return false;
        }

        if (!Arr::has($input, $this->column)) {
            return false;
        }

        $rules["{$this->column}.*"] = $fieldRules;
        $attributes["{$this->column}.*"] = __('Value');

        $rules["{$this->column}"][] = 'array';

        $attributes["{$this->column}"] = $this->label;

        return validator($input, $rules, $this->getValidationMessages(), $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function setupScript()
    {
        $this->script = <<<JS

        (function () {
            var tbody = document.querySelector('tbody.list-{$this->column}-table');
            var tplElement = document.querySelector('template.{$this->column}-tpl');

            // Agrega una fila nueva desde la plantilla, después de afterRow o al final, y devuelve su input
            function addRow(afterRow) {
                var clone = htmlToElement(tplElement.innerHTML);
                if (afterRow && afterRow.parentNode === tbody) {
                    tbody.insertBefore(clone, afterRow.nextElementSibling);
                } else {
                    tbody.appendChild(clone);
                }

                return clone.querySelector('input');
            }

            // Lleva el foco a un input y deja el cursor al final del texto
            function focusAtEnd(input) {
                if (!input) {
                    return;
                }
                input.focus();
                var length = input.value.length;
                try {
                    input.setSelectionRange(length, length);
                } catch (e) {
                    // algunos tipos de input no admiten selección; basta con el foco
                }
            }

            // Devuelve el input de la fila si el evento viene de un input de esta lista
            function rowInput(event) {
                var target = event.target;
                if (!target || target.tagName !== 'INPUT') {
                    return null;
                }
                var row = target.closest('tr');
                if (!row || row.parentNode !== tbody) {
                    return null;
                }

                return target;
            }

            document.querySelector('.{$this->column}-add').addEventListener('click', function () {
                focusAtEnd(addRow());
            });

            tbody.addEventListener('click', function (event) {
                var remove = event.target.closest('.{$this->column}-remove');
                if (remove && tbody.contains(remove)) {
                    remove.closest('tr').remove();
                }
            });

            // Teclado: Enter pasa a la fila siguiente o la crea; Supr/Retroceso en una fila vacía la elimina
            tbody.addEventListener('keydown', function (event) {
                var input = rowInput(event);
                if (!input || event.isComposing) {
                    return;
                }
                var row = input.closest('tr');

                if (event.key === 'Enter' && !event.shiftKey && !event.ctrlKey && !event.altKey && !event.metaKey) {
                    // evita que Enter envíe el formulario
                    event.preventDefault();
                    var next = row.nextElementSibling;
                    if (next && next.querySelector('input')) {
                        focusAtEnd(next.querySelector('input'));
                    } else {
                        focusAtEnd(addRow(row));
                    }

                    return;
                }

                if ((event.key === 'Delete' || event.key === 'Backspace') && input.value === '') {
                    var prev = row.previousElementSibling;
                    var after = row.nextElementSibling;
                    // se conserva siempre al menos una fila para poder seguir escribiendo
                    if (!prev && !after) {
                        return;
                    }
                    event.preventDefault();
                    row.remove();
                    var target = (event.key === 'Backspace' ? (prev || after) : (after || prev));
                    focusAtEnd(target ? target.querySelector('input') : null);
                }
            });

            // Pegar texto de varias líneas crea una fila por línea
            tbody.addEventListener('paste', function (event) {
                var input = rowInput(event);
                if (!input) {
                    return;
                }
                var clipboard = event.clipboardData || window.clipboardData;
                if (!clipboard) {
                    return;
                }
                var text = clipboard.getData('text') || '';
                var lines = text.split(/\\r\\n|\\r|\\n/);
                // el salto de línea final (típico al copiar de una hoja de cálculo) no crea fila vacía
                if (lines.length > 1 && lines[lines.length - 1] === '') {
                    lines.pop();
                }
                if (lines.length < 2) {
                    // una sola línea: se deja el pegado normal del navegador
                    return;
                }
                event.preventDefault();

                // la primera línea reemplaza la selección actual del input, como un pegado normal
                var value = input.value;
                var start = input.selectionStart;
                var end = input.selectionEnd;
                if (start === null || end === null) {
                    start = end = value.length;
                }
                input.value = value.slice(0, start) + lines[0] + value.slice(end);

                var row = input.closest('tr');
                var last = input;
                for (var i = 1; i < lines.length; i++) {
                    last = addRow(row);
                    last.value = lines[i];
                    row = last.closest('tr');
                }
                focusAtEnd(last);
            });
        })();
JS;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($value)
    {
        $value = (array) parent::prepare($value);

        $values = array_values($value);
        if (count($values) == 1 && empty($values[0])) {
            return [];
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $this->addSortable('tbody.list-', '-table');
        view()->share('options', $this->options);

        $this->setupScript();

        Admin::style('td .form-group {margin-bottom: 0 !important;}');

        return parent::render();
    }
}
