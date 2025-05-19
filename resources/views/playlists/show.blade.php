@extends('layouts.app')

@section('content')
<div class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar p-6">
        <div class="flex justify-between items-center mb-8">
            <a href="{{ url('/dashboard') }}" class="text-gray-400 hover:text-white mr-4">← На главную</a>
            <h1 class="text-3xl font-bold text-white">{{ $playlist->name }}</h1>
            <form action="{{ route('playlists.destroy', $playlist) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-400">Удалить</button>
            </form>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($playlist->songs as $song)
            <div class="card p-4 rounded-lg relative group">
                <div class="relative mb-4">
                    @if($song->cover_path)
                    <img src="{{ asset($song->cover_path) }}" alt="Album Cover" class="w-full rounded shadow-lg">
                    @else
                    <img src="https://via.placeholder.com/160" alt="Album Cover" class="w-full rounded shadow-lg">
                    @endif
                </div>
                <h3 class="font-semibold text-white truncate">{{ $song->title }}</h3>
                <p class="text-gray-400 text-sm mt-1 truncate">{{ $song->artist }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content flex-1 p-8 overflow-y-auto">
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-6 text-white">{{ $playlist->name }}</h2>
            
            @if($songs->count() > 0)
                <div class="grid grid-cols-1 gap-4">
                    @foreach($songs as $song)
                        <div class="bg-gray-800 p-4 rounded-lg flex items-center">
                            @if($song->cover_path)
                                <img src="{{ asset($song->cover_path) }}" alt="Album Cover" class="w-16 h-16 rounded mr-4">
                            @endif
                            <div>
                                <h3 class="text-lg font-semibold">{{ $song->title }}</h3>
                                <p class="text-gray-400">{{ $song->artist->name ?? 'Unknown' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400">В этом плейлисте пока нет песен</p>
            @endif
        </div>
    </div>
</div>
@endsection