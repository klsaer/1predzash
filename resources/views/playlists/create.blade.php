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
                <li><a href="{{ url('/') }}" class="text-white font-semibold">Главная</a></li>
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
            <h2 class="text-2xl font-bold mb-6 text-white">Создать новый плейлист</h2>
            
            <form action="{{ route('playlists.store') }}" method="POST" class="max-w-md bg-gray-800 p-6 rounded-lg">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-gray-300 mb-2">Название плейлиста</label>
                    <input type="text" name="name" id="name" required 
                           class="w-full bg-gray-700 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded transition duration-200">
                    Создать плейлист
                </button>
            </form>
        </div>
    </div>
</div>
@endsection