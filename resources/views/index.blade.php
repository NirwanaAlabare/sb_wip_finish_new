@extends('layouts.index')

@section('content')
    <livewire:order-list/>
@endsection

@section('custom-script')
    <script>
        Livewire.on('showFilterModal', () => {
            showFilterModal();

            Livewire.emit('loadingStop');
        });

        Livewire.on('loadingStart', () => {
            if (document.getElementById('loading-order-list')) {
                $('#loading-order-list').removeClass('hidden');
            }
            if (document.getElementById('loading-rft')) {
                $('#loading-rft').removeClass('hidden');
                $('#content-rft').addClass('hidden');
            }
            if (document.getElementById('loading-defect')) {
                $('#loading-defect').removeClass('hidden');
                $('#content-defect').addClass('hidden');
            }
            if (document.getElementById('loading-defect-history')) {
                $('#loading-defect-history').removeClass('hidden');
                $('#content-defect-history').addClass('hidden');
            }
            if (document.getElementById('loading-reject')) {
                $('#loading-reject').removeClass('hidden');
                $('#content-reject').addClass('hidden');
            }
            if (document.getElementById('loading-rework')) {
                $('#loading-rework').removeClass('hidden');
                $('#content-rework').addClass('hidden');
            }
            if (document.getElementById('loading-profile')) {
                $('#loading-profile').removeClass('hidden');
                $('#content-profile').addClass('hidden');
            }
            if (document.getElementById('loading-history')) {
                $('#loading-history').removeClass('hidden');
                $('#content-history').addClass('hidden');
            }
            if (document.getElementById('loading-undo')) {
                $('#loading-undo').removeClass('hidden');
                $('#content-undo').addClass('hidden');
            }
        });

        Livewire.on('loadingStop', () => {
            if (document.getElementById('loading-order-list')) {
                $('#loading-order-list').addClass('hidden');
            }
            if (document.getElementById('loading-rft')) {
                $('#loading-rft').addClass('hidden');
                $('#content-rft').removeClass('hidden');
            }
            if (document.getElementById('loading-defect')) {
                $('#loading-defect').addClass('hidden');
                $('#content-defect').removeClass('hidden');
            }
            if (document.getElementById('loading-defect-history')) {
                $('#loading-defect-history').addClass('hidden');
                $('#content-defect-history').removeClass('hidden');
            }
            if (document.getElementById('loading-reject')) {
                $('#loading-reject').addClass('hidden');
                $('#content-reject').removeClass('hidden');
            }
            if (document.getElementById('loading-rework')) {
                $('#loading-rework').addClass('hidden');
                $('#content-rework').removeClass('hidden');
            }
            if (document.getElementById('loading-profile')) {
                $('#loading-profile').addClass('hidden');
                $('#content-profile').removeClass('hidden');
            }
            if (document.getElementById('loading-history')) {
                $('#loading-history').addClass('hidden');
                $('#content-history').removeClass('hidden');
            }
            if (document.getElementById('loading-undo')) {
                $('#loading-undo').addClass('hidden');
                $('#content-undo').removeClass('hidden');
            }
        });
    </script>
@endsection
