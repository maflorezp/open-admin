<div {!! $attributes !!} aria-labelledby="{{ $id }}-title">
    <div class="{{ $dialogClass }}" role="document">
        <div class="modal-content" style="border-radius: 5px;">
            <div class="modal-header">
                <h4 class="modal-title" id="{{ $id }}-title">{{ $title }}</h4>
                <button type="button" class="btn btn-light close" data-bs-dismiss="modal" aria-label="{{ admin_trans('admin.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if ($body !== '')
                    {!! $body !!}
                @endif
                @if ($showLoader)
                    <div class="loading text-center" style="margin: 50px;">
                        <div class="icon-spin">
                            <i class="icon-spinner icon-spin icon-3x icon-fw"></i>
                        </div>
                    </div>
                @endif
            </div>
            @if ($footer !== '')
                <div class="modal-footer">
                    {!! $footer !!}
                </div>
            @endif
        </div>
    </div>
</div>
