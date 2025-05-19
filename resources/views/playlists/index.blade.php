@extends('layouts.app')

@section('content')
<div class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar p-6">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white">Music App</h1>
        </div>
        <nav>
            <ul class="space-y-4">
                <li><a href="{{ route('dashboard') }}" class="text-white font-semibold">Главная</a></li>
                <li><a href="{{ route('profile.edit') }}" class="text-white font-semibold">Профиль</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-white">Выход</button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content flex-1 p-8 overflow-y-auto">
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-6 text-white">Мои плейлисты</h2>
            
            @if($playlists->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($playlists as $playlist)
                        <div class="bg-gray-800 p-4 rounded-lg">
                            <h3 class="text-xl font-semibold text-white">{{ $playlist->name }}</h3>
                            <a href="{{ route('playlists.show', $playlist) }}" class="text-purple-400 hover:text-purple-300 mt-2 inline-block">Открыть</a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400">У вас пока нет плейлистов</p>
            @endif
        </div>
    </div>
</div>
@endsection
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Добавляем кнопку возврата -->
            <div class="mb-4">
                <a href="{{ url('/') }}" class="text-gray-600 hover:text-gray-900">
                    ← На главную
                </a>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                // ... existing code ...
            </div>
        </div>
    </div>
</x-app-layout>