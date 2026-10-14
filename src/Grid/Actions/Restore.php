<?php

namespace OpenAdmin\Admin\Grid\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OpenAdmin\Admin\Actions\Response;
use OpenAdmin\Admin\Actions\RowAction;

/**
 * Restaura un registro borrado con SoftDeletes. Es opt-in: solo aparece si la grilla
 * llama $actions->enableRestore() y la fila está en la papelera.
 */
class Restore extends RowAction
{
    public $icon = 'icon-trash-restore';

    /**
     * @return array|null|string
     */
    public function name()
    {
        return __('admin.restore');
    }

    public function dialog()
    {
        $options = [
            'type'                => 'warning',
            'showCancelButton'    => true,
            'confirmButtonColor'  => '#00a65a',
            'confirmButtonText'   => __('admin.confirm'),
            'showLoaderOnConfirm' => true,
            'cancelButtonText'    => __('admin.cancel'),
        ];
        $this->confirm(__('admin.restore_confirm'), '', $options);
    }

    /**
     * @param Model $model
     *
     * @return Response
     */
    public function handle(Model $model)
    {
        // Solo modelos con SoftDeletes; un registro que no está en la papelera no se toca
        if (!method_exists($model, 'trashed') || !$model->trashed()) {
            return $this->response()->error(__('admin.restore_failed'));
        }

        try {
            DB::transaction(function () use ($model) {
                $model->restore();
            });
        } catch (\Exception $exception) {
            return $this->response()->error(__('admin.restore_failed').' : '.$exception->getMessage());
        }

        return $this->response()->success(__('admin.restore_succeeded'))->refresh();
    }
}
