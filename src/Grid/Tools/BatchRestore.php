<?php

namespace OpenAdmin\Admin\Grid\Tools;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use OpenAdmin\Admin\Actions\BatchAction;

/**
 * Restaura en lote los registros seleccionados que estén en la papelera (SoftDeletes).
 * Es opt-in: solo aparece con $batch->enableRestore() y si el modelo usa SoftDeletes.
 */
class BatchRestore extends BatchAction
{
    public $icon = 'icon-trash-restore';

    public function __construct()
    {
        // El constructor padre inicializa el interactor que necesita dialog()
        parent::__construct();

        $this->name = __('admin.batch_restore');
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
     * @param Collection $collection
     *
     * @return \OpenAdmin\Admin\Actions\Response
     */
    public function handle(Collection $collection)
    {
        // Solo los seleccionados que de verdad están en la papelera
        $trashed = $collection->filter(function ($model) {
            return method_exists($model, 'trashed') && $model->trashed();
        });

        try {
            DB::transaction(function () use ($trashed) {
                $trashed->each(function ($model) {
                    $model->restore();
                });
            });
        } catch (\Exception $exception) {
            return $this->response()->error(__('admin.restore_failed').' : '.$exception->getMessage());
        }

        return $this->response()->success(__('admin.restore_succeeded'))->refresh();
    }
}
