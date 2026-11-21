@extends('layouts.app')

@section('title', 'Penawaran Saya')

@section('content')
<div class="card">
    <h1>Penawaran Saya</h1>
</div>

@if ($bids->isEmpty())
    <div class="card"><p>Anda belum mengajukan penawaran.</p></div>
@else
    @foreach ($bids as $b)
        <div class="card">
            <h2>
                <a href="{{ route('projects.show', $b->project_id) }}">{{ $b->project->judul }}</a>
                <span class="badge {{ $b->project->status === 'terbuka' ? 'open' : 'closed' }}">{{ $b->project->status }}</span>
                @if ($b->is_winner)
                    <span class="badge open">Pemenang</span>
                @endif
            </h2>
            <p class="muted">
                Klien: {{ $b->project->client->nama }} | 
                Harga: Rp {{ number_format($b->harga, 0, ',', '.') }} | 
                Durasi: {{ $b->durasi }} hari | 
                Batas: {{ $b->project->batas_lelang->format('Y-m-d') }}
            </p>
        </div>
    @endforeach
@endif
@endsection
