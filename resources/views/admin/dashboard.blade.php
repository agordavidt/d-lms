<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white rounded-lg shadow p-6">
                <h1 class="text-3xl font-bold mb-4">Admin Dashboard</h1>
                <p class="text-gray-600 mb-4">Welcome, {{ auth()->user()->name }}!</p>
                <p class="mb-4">Role: <span class="font-semibold">{{ auth()->user()->role }}</span></p>
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>