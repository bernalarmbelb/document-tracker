@php
    $tabs = [
        'resolutions'   => 'Resolutions',
        'ordinances'    => 'Ordinances',
        'minutes'       => 'Minutes',
        'communications'=> 'Communications',
        'sangguniang'   => 'Sangguniang Activities',
        'users'         => 'Users',
    ];
@endphp
<div class="tm-segbtns">
    @foreach($tabs as $key => $label)
        <a href="{{ url('archive/'.$key) }}" class="tm-btn {{ ($active ?? '')==$key ? 'tm-btn-primary' : 'tm-btn-outline' }}">{{ $label }}</a>
    @endforeach
</div>
