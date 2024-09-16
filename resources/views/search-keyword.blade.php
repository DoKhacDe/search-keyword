<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <title>Search Keyword</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-900 text-gray-100">
<div class="container mx-auto pt-[50px] pb-[100px]">
    <h1 class="text-primary-400 text-center text-3xl font-extrabold mb-8">Search Keyword</h1>

    <form action="{{ route('search-keyword') }}" method="POST" enctype="multipart/form-data" class="max-w-lg mx-auto p-8 bg-gray-800 shadow-lg rounded-lg">
        @csrf
        <div class="mb-6">
            <label for="file" class="block text-sm font-medium text-gray-300">Chọn file</label>
            <div class="relative mt-1">
                <input type="file" name="file" id="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                <div class="flex items-center justify-center border border-gray-600 bg-gray-700 rounded-md py-2 px-3 text-gray-300 text-sm">
                    <span id="file-name">Chưa chọn file</span>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <label for="api_key" class="block text-sm font-medium text-gray-300">API Key</label>
            <input type="text" name="api_key" id="api_key" placeholder="Nhập API Key" class="py-2 px-3 mt-1 block w-full border border-gray-600 rounded-md shadow-sm bg-gray-800 text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Submit
        </button>
    </form>

    @if(isset($results) && !empty($results))
        <div class="flex justify-between items-center mt-12">
            <h2 class="text-2xl font-semibold text-gray-100">Results</h2>
            <div class="mt-8 text-center">
                <a href="{{ route('export') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Download Excel
                </a>
            </div>
        </div>
        <div class="max-h-[600px] mt-8 relative" style="height: 600px; overflow-y: auto">
            <table class="min-w-full divide-y divide-gray-700 table-auto bg-gray-800 shadow-md rounded-lg">
                <thead class="bg-gray-700 sticky top-0">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                        Từ khóa
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                        Domain
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                        Thứ hạng
                    </th>
                </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                @foreach($results as $result)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-100">
                            {{ $result['q'] ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-500 underline">
                            <a href="{{ $result['domain']}}">{{ $result['domain'] ?? 'N/A' }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                            {{ $result['position'] ?? 'N/A' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-center text-gray-500 mt-4">No results found.</p>
    @endif

    @if(isset($error))
        <div class="w-full text-center">
            <span class="text-red-500">{{$error}}</span>
        </div>
    @endif
</div>

<script>
    document.getElementById('file').addEventListener('change', function() {
        const fileName = this.files.length > 0 ? this.files[0].name : 'Chưa chọn file';
        document.getElementById('file-name').textContent = fileName;
    });
</script>
</body>
</html>
