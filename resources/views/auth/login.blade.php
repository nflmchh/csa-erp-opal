<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SevenKey ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-indigo-950 to-gray-900 flex items-center justify-center p-4">

    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-indigo-500 rounded-2xl flex items-center justify-center font-bold text-xl text-white mx-auto mb-3 shadow-lg">7K</div>
            <h1 class="text-2xl font-bold text-white tracking-tight">SevenKey ERP</h1>
            <p class="text-indigo-300 text-sm mt-1">Fashion Retail Management System</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">

            @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                {{ session('error') }}
            </div>
            @endif

            @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="user@sevenkey.id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-red-500 @enderror"
                    >
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                        Ingat saya
                    </label>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Lupa password?</a>
                    @endif
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm mt-2">
                    Masuk
                </button>
            </form>

        </div>

        <!-- {{-- Demo accounts --}}
        <div class="mt-4 bg-white/10 rounded-xl p-4 text-xs text-indigo-200 space-y-1">
            <p class="font-semibold text-indigo-100 mb-2">Akun Demo:</p>
            <p>Superadmin: <span class="font-mono text-white">superadmin@sevenkey.id</span></p>
            <p>Owner: <span class="font-mono text-white">owner@sevenkey.id</span></p>
            <p>Admin Gudang: <span class="font-mono text-white">admin.gudang@sevenkey.id</span></p>
            <p>Kasir: <span class="font-mono text-white">kasir@sevenkey.id</span></p>
            <p class="text-indigo-300">Password semua akun: <span class="font-mono text-white">password</span></p>
        </div> -->

    </div>

</body>
</html>
